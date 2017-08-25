/* Tin Can configuration */

//
// ActivityID that is sent for the statement's object
//
TC_COURSE_ID = "--ActivityID--";

//
// CourseName for the activity
//
TC_COURSE_NAME = {
    "en-US": "--ActivityName--"
};

//
// CourseDesc for the activity
//
TC_COURSE_DESC = {
    "en-US": "--ActivityDesc--"
};

//
// Pre-configured LRSes that should receive data, added to what is included
// in the URL and/or passed to the constructor function.
//
// An array of objects where each object may have the following properties:
//
//    endpoint: (including trailing slash '/')
//    auth:
//    allowFail: (boolean, default true)
//    version: (string, defaults to high version supported by TinCanJS)
//
TC_RECORD_STORES = [
    /*{
        endpoint: "https://cloud.scorm.com/ScormEngineInterface/TCAPI/public/",
        auth:     "Basic VGVzdFVzZXI6cGFzc3dvcmQ=",
        version:  "0.95"
    }*/
];
