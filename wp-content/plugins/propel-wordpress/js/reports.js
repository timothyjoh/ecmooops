function drawTrafficDetail(ids, orgId, mediumsId, visitorId, searchId, socialId) {
   query({
      'ids': ids,
      'dimensions': 'ga:source,ga:medium,ga:socialNetwork,ga:userType,ga:socialNetwork',
      'metrics': 'ga:sessions',
      'start-date': '30daysAgo',
      'end-date': 'yesterday'
   })
   .then(function(response) {
      var total = 0;
      orgRows = [];
      map = {};
      social = {};
      search = {};
      userTypes = { 'Returning Visitor': 0, 'New Visitor': 0}
      response.rows.forEach(function(row, i) {
         console.log(row);
         var name = row[1];
         var count = parseInt(row[5],10);
         if (name == "(none)") name = "direct";
         if ( row[4] != '(not set)') name = "social";
         if ( !(name in map) ) map[name] = 0;
         map[name] += count;
         total += count;
         userTypes[row[3]] += count;
         
         // track social media referrer
         if ( row[1] == 'referral' &&  row[4] != '(not set)' ) {
            if ( !( row[4] in social) ) social[ row[4] ] = 0;
            social[ row[4] ] += count;
         }
         
         // track search engine used
         if ( row[1] == 'organic') {
            if ( !( row[0] in search) ) search[ row[0] ] = 0;
            search[ row[0] ] += count;
         }
      });
      
      // setup channel org chart
      var data = new google.visualization.DataTable();
      data.addColumn('string', 'Name');
      data.addColumn('string', 'Parent');
      orgRows.push([{v: 'Channels', f:'<h4 class="chart-lbl">Channels</h4><div>'+total+'</div>'}, ""]); 
      for (var key in map) {
         orgRows.push([{v: key, f:'<h4 class="chart-lbl">'+key+'</h4><div>'+map[key]+'</div>'}, "Channels"]); 
      }
      data.addRows( orgRows );
      var chart = new google.visualization.OrgChart(document.getElementById(orgId));
      chart.draw(data,{allowHtml:true, width: 200});
      
      // setup pie charts
      var all = [
         {data: userTypes, title: 'Visitor Type', elementId: visitorId}, 
         {data: map, title: 'Traffic Mediums', elementId: mediumsId}, 
         {data: social, title: 'Social Networks', elementId: socialId}, 
         {data: search, title: 'Search Engines', elementId: searchId} ];
      jQuery.each(all, function(idx, val) {
         var mData = new google.visualization.DataTable();
         mData.addColumn('string', 'Name');
         mData.addColumn('number', 'Total');
         for (var key in val.data) {
            mData.addRow([key, val.data[key]]);
         }
         var chart = new google.visualization.PieChart(document.getElementById(val.elementId));
         chart.draw(mData, {title: val.title, width:200,height:200,legend:'none'});
      });
   });
}

function drawBrowserUsage(ids, chartId) {
   var options = {
      'width':400,
      'height':300};
   query({
      'ids': ids,
      'dimensions': 'ga:browser',
      'metrics': 'ga:pageviews',
      'max-results': 5
   })
   .then(function(response) {
      
      var data = new google.visualization.DataTable();
      data.addColumn('string', 'Browser');
      data.addColumn('number', 'Sessions');
      var rows = [];
      response.rows.forEach(function(row, i) {
         rows.push( [row[0], parseInt(row[1],10)] );
      });
      data.addRows(rows);
      
      var chart = new google.visualization.PieChart(document.getElementById(chartId));
      chart.draw(data, options);
   });
}

function query(params) {
   return new Promise(function(resolve, reject) {
      var data = new gapi.analytics.report.Data({query: params});
      data.once('success', function(response) { resolve(response); })
      .once('error', function(response) { reject(response); })
      .execute();
   });
}