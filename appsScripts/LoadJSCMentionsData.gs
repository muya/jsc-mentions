/**
* This script will be set up to run periodically to fetch JSCMentionsData
* from Gmail. More info can be obtained at: https://github.com/muya/jsc-mentions
*/


/*
* fetch email with required attachment in Gmail
* fetch the attachments from Gmail
* load the data into a spreadsheet
*/
function loadJSCMentionsData() {
  //fetch attachments from email
  var attachments = fetchJSCMentionsAttachments();
  
  if(attachments == null){
    Logger.log('No JSC Mentions Data attachments found. Exiting...');
    return;
  }
  
  Logger.log('Data found: ' + attachments);
  
  //parse the attachments
  Logger.log('About to start processing attachments ');
  Logger.log('found %s attachment(s)', attachments.length);
  processJSCMentionsAttachments(attachments);
}

/**
* function to determine the attachment type from the name
* looks for specific patterns in the attachment
*/
function getAttachmentType(attachmentName){
  var MENTIONS_PER_LOCATION = "MENTIONS_PER_LOCATION";
  var MENTIONS_PER_PLATFORM = "MENTIONS_PER_PLATFORM";
  
  if(attachmentName.indexOf("mentions_per_platform") > -1){
    return MENTIONS_PER_PLATFORM;
  }
  else if (attachmentName.indexOf("mentions_per_location") > -1){
    return MENTIONS_PER_LOCATION;
  }
  else{
    return null;
  }
}

/**
* this function parses the JSC mentions data and loads it into 
* a spreadsheet
*/
function processJSCMentionsAttachments(attachments){
  //first fetch all the data that is required, 
  //then create the required sheet, and insert the data
  
  var mentionsPerLocationData = null;
  var mentionsPerPlatformData = null;
  
  //loop through the attachments
  for(var i=0; i < attachments.length; i++){
    Logger.log('Attachment # ' + i);
    var currAttachment = attachments[i];
    var currAttachmentName = attachments[i].getName();
    var currAttachmentSize = attachments[i].getSize();
    Logger.log('Attachment # ' + i + ' contains the attachment "%s" (%s bytes)',
                   currAttachmentName, currAttachmentSize);
    
    //check what type the attachment is
    var attachmentType = getAttachmentType(currAttachmentName);
    Logger.log("attachment type returned: " + attachmentType);
    
    //our data is in JSON
    var currDataAsString = currAttachment.getDataAsString();
    Logger.log('data as string: ' + currDataAsString);
    var currJSONData = Utilities.jsonParse(currDataAsString);
    Logger.log('Obtained JSON: ' + currJSONData);
    
    //parse the data according to the type
    if (attachmentType === "MENTIONS_PER_LOCATION"){
      Logger.log("This is a 'mentions per location' attachment");
      mentionsPerLocationData = parseMentionsPerLocationData(currJSONData);
    }
    else if (attachmentType === "MENTIONS_PER_PLATFORM"){
      Logger.log("This is a 'mentions per platform' attachment");
      mentionsPerPlatformData = parseMentionsPerPlatformData(currJSONData);
    }
    else{
      Logger.log('unknown attachment type: ');
      return null;
    }
  }
  
  //now create the spreadsheet, and start adding data
  //create the sheet
  var dateToday = Utilities.formatDate(new Date(), "EAT", "yyyy-MM-dd HHmmss");
  Logger.log(dateToday);
  var spreadsheetName = 'JSC Mentions-' + dateToday;
  var spreadsheet = SpreadsheetApp.create(spreadsheetName);
  var spreadsheetID = spreadsheet.getId();
  
  //load the data into the sheet
  if(mentionsPerLocationData !== null){
    Logger.log('about to insert mentions per location data: ' + JSON.stringify(mentionsPerLocationData));
    //we need to create a sheet for this
    var mentionsPerLocationSheetName = 'Mentions per Location';
    var mentionsPerLocationSheet = spreadsheet.getSheetByName(mentionsPerLocationSheetName);
    
    if(!mentionsPerLocationSheet){
      mentionsPerLocationSheet = spreadsheet.insertSheet(mentionsPerLocationSheetName);
    }
    
    //add header row
    var mentionsPerLocationHeaders = ['LocationID', 'Location Name', 'Number of Mentions'];
    
    //add actual data
    mentionsPerLocationSheet = insertJSCMentionsData(mentionsPerLocationSheet, mentionsPerLocationHeaders, mentionsPerLocationData);
    Logger.log("completed insertion of mentions per location data...");
    
  }
  
  if(mentionsPerPlatformData !== null){
    Logger.log('about to insert mentions per platform data: ' + JSON.stringify(mentionsPerPlatformData));
    
    //create a sheet for this
    var mentionsPerPlatformSheetName = 'Mentions per Platform';
    var mentionsPerPlatformSheet = spreadsheet.getSheetByName(mentionsPerPlatformSheetName);
    
    if(!mentionsPerPlatformSheet){
      mentionsPerPlatformSheet = spreadsheet.insertSheet(mentionsPerPlatformSheetName);
    }
    
    //add header row
    var mentionsPerPlatformHeaders = ['PlatformID', 'Platform Name', 'Number of Mentions'];
    
    //add actual data
    mentionsPerPlatformSheet = insertJSCMentionsData(mentionsPerPlatformSheet, mentionsPerPlatformHeaders, mentionsPerPlatformData);
    Logger.log("completed insertion of mentions per platform data...");
  }
}

/**
* function to insert the data into current sheet and return the
* sheet with inserted data
*/
function insertJSCMentionsData(currentActiveSheet, headerContent, dataContent){
  //add header
  currentActiveSheet.appendRow(headerContent);
  
  //add data
  for(var i=0; i<dataContent.length; i++){
    currentActiveSheet.appendRow(dataContent[i]);
  }
  return currentActiveSheet;
}


/**
* function to parse mentions per platform data
* loops through the data, looking for the expected keys, 
* and loads them into a JSON array
*/
function parseMentionsPerPlatformData(JSONData){
  Logger.log("about to start parse mentions per platform data..." + JSON.stringify(JSONData));
  var mentionsPerPlatformData = [];
  for(d in JSONData){
    mentionsPerPlatformData[d] = [JSONData[d].platformID, JSONData[d].platformName, JSONData[d].mentions];
  }
  
  Logger.log("successfully parsed mentions per platform data..." + JSON.stringify(mentionsPerPlatformData));
  return mentionsPerPlatformData;
}

/**
* function to parse mentions per location data
* loops through the data, looking for the expected keys, 
* and loads them into a JSON array
*/
function parseMentionsPerLocationData(JSONData){
  Logger.log("about to start parse mentions per location data..." + JSON.stringify(JSONData));
  var mentionsPerLocationData = [];
  for(d in JSONData){
//    Logger.log('curr row data: %s, %s, %s', JSONData[d].locationID, JSONData[d].locationName, JSONData[d].mentions);
    mentionsPerLocationData[d] = [JSONData[d].locationID, JSONData[d].locationName, JSONData[d].mentions];
  }
  
//  Logger.log("successfully parsed mentions per location data ..." + JSON.stringify(mentionsPerLocationData));
  return mentionsPerLocationData;
}

/**
* function to fetch JSCMentions attachments from inbox
* uses the normal Gmail search criteria to find required
* emails
*/
function fetchJSCMentionsAttachments(){
  //our emails are automatically labeled JSCMentions
  //we'll search for unread ones since the others will be archived
  //once processed
  var GMAIL_SEARCH_CRITERIA = 'label:jscmentions is:unread';
  
  //apps scripts make this veeeery easy
  var threads = GmailApp.search(GMAIL_SEARCH_CRITERIA);
  
  Logger.log('Thread count: ' + threads.length);
  
  if(threads.length < 1){
    Logger.log('No matching emails found.');
    return null;
  }
  
  //get the messages last thread 
  var lastMessageThread = threads[threads.length -1];
  
  var messages = lastMessageThread.getMessages();
  lastMessageThread.markRead();
  lastMessageThread.moveToArchive();
  
  //get attachment from last message
  var attachments = messages[messages.length -1].getAttachments();
  
  return attachments;
}