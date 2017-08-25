# This is an xAPI wrapper for PROPEL

### using SCORM content

1. Empty all content in the **scormcontent** folder
2. Unzip the scorm package and throw all the contents into the **scormcontent** folder

#### If the starting page of your content is named anything other than `index.html` then you will need to follow the following steps

1. Open the file `scormdriver/indexAPI.html`
2. Find this section:
  ```
    <script language="JavaScript1.2">
    
    strContentLocation = "../scormcontent/index.html";  //Put the link to the start of the content here.
    //strContentLocation = "TestAllFunctions.htm";
  ``` 
  And change the path from `index.html` to the starting page of your content

#### To override the SCORM calls to xAPI calls (GOMO specific)

Open up the starting page, this javascript is what overrides GOMO's scorm calls
  ```
    <script type="text/javascript">
      function SetSCOComplete()
      {
        var SD = window.parent;
        SD.SetReachedEnd();
        SD.CommitData();
      }
      function SetBookmark(gomo) {
        var SD = window.parent,
            loc = window.location.href
        ;

        SD.SetBookmark(
            loc.substring(loc.toLowerCase().lastIndexOf("/scormcontent/") + 14, loc.length),
            gomo.pageTitle
        );
        SD.CommitData();
      }
      pipwerks.SCORM.set = function(c,b) {
        console.log(["SCORM set called",c,b]);
        if (c == 'status' && b == 'completed'){
          SetSCOComplete();
        }
        if (c == 'location'){
          console.log("Location tracking");
          SetBookmark(JSON.parse(b));
        }
      }
    </script>
  ```

  #### If you have multipage HTML content, do the following

  1. copy the **SetBookmark** function above to each new page
  2. copy the **SetComplete** function above to the final page (to mark completion)