/* Tin Can configuration */

//
// ActivityID that is sent for the statement's object
//
TC_COURSE_ID = "http://propel.scitent.us/gomo-no-special-tracking";

//
// CourseName for the activity
//
TC_COURSE_NAME = {
    "en-US": "Gomo no Special tracking"
};

//
// CourseDesc for the activity
//
TC_COURSE_DESC = {
    "en-US": "Gomo no Special tracking"
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
//
TC_RECORD_STORES = [
    // {
    //     endpoint: "https://cloud.scorm.com/ScormEngineInterface/TCAPI/public/",
    //     auth:     "Basic VGVzdFVzZXI6cGFzc3dvcmQ="
    // }
];
