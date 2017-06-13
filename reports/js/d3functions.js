d3.selection.prototype.moveToFront = function() {
  return this.each(function(){
    this.parentNode.appendChild(this);
  });
};

var monthNames = [
  "Jan", "Feb", "Mar",
  "Apr", "May", "Jun", "Jul",
  "Aug", "Sept", "Oct",
  "Nov", "Dec"
];

var check = "";

switch (frompage) {
    case 'detailstats' : 
        var parseDate = d3.time.format("%Y-%m-%d").parse;

        $('[data-area-visit]').each(function (i, svg) {
            var twidth = this.clientWidth;
            var theight = this.clientHeight;            

            var margin = {top: 20, right: 20, bottom: 60, left: 50},
                width = twidth - margin.left - margin.right,
                height = theight - margin.top - margin.bottom;

            var $svg = $(svg);
            var data = JSON.parse(JSON.stringify(graphdata)); //clone

            data.forEach(function(d) {
                d.date = parseDate(d.date.slice(0,10));
                d.user_count = +d.user_count;
                d.user_d_count = +d.user_d_count;
            });

            data.sort(function (a, b) {
                return a.date - b.date;
            });

            var x = d3.time.scale()
                .range([0, width]);

            var y = d3.scale.linear()
                .range([height, 0]);
            
            //x.domain(d3.extent(data, function(d) { return d.date; }));
            x.domain(d3.extent(data, function(d) { return d.date; }));
            y.domain([0, d3.max(data, function(d) { return ((d.user_count < d.user_d_count) ? d.user_d_count : d.user_count); })]);            

            var xAxis = d3.svg.axis()
                .scale(x)
                .orient("bottom")
                .ticks(d3.time.day, 1);

            var p1 = x.domain()[0];
            var p2 = x.domain()[1];
            var p1Str = p1.getDate() + " " + monthNames[p1.getMonth()] + " " +p1.getFullYear();
            var p2Str = p2.getDate() + " " + monthNames[p2.getMonth()] + " " +p2.getFullYear();

            $('.visitPeriod').html("Period : "+p1Str+" - " +p2Str);

            var yAxis = d3.svg.axis()
                .scale(y)
                .orient("left");

            var bisectDate = d3.bisector(function(d) { return d.date; }).left;

            var svg = d3.select(svg)
                .attr("width", width + margin.left + margin.right)
                .attr("height", height + margin.top + margin.bottom)
                .append("g")
                .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

            /*svg.attr("preserveAspectRatio", "xMinYMin meet")
               .attr("viewBox", "0 0 600 400")
               .classed("svg-content-responsive", true);*/

            svg.append("g")
                .attr("class", "x axis")
                .attr("transform", "translate(0," + height + ")")
                .call(xAxis);

            svg.append("g")
                .attr("class", "y axis")
                .call(yAxis)
                .append("text")
                .attr("transform", "rotate(-90)")
                .attr("y", 6)
                .attr("dy", ".71em")
                .style("text-anchor", "end")
                ;

            var focus = svg.append("g")
              .attr("class", "focus")
              .style("display", "none");

            focus.append("circle")
              .attr("r", 4.5);

            focus.append("text")
              .attr("x", 0)
              .attr("y", -9)
              .attr("dy", ".35em");

            var focus2 = svg.append("g")
              .attr("class", "focus")
              .style("display", "none");

            focus2.append("circle")
              .attr("r", 4.5);

            focus2.append("text")
              .attr("x", 0)
              .attr("y", 9)
              .attr("dy", ".35em");  

            svg.on("mouseover", function() { focus.style("display", null); focus2.style("display", null);})
               .on("mouseout", function() { focus.style("display", "none"); focus2.style("display", "none");})
               .on("mousemove", mousemove);

            function mousemove() {
                var x0 = x.invert(d3.mouse(this)[0]),
                    i = bisectDate(data, x0, 1),
                    d0 = data[i - 1],
                    d1 = data[i];
                if (typeof d1 !== 'undefined'){
                    var d = x0 - d0.date > d1.date - x0 ? d1 : d0;
                } else {
                    var d = d0;
                }
                focus.attr("transform", "translate(" + x(d.date) + "," + y(d.user_count) + ")");
                focus.select("text").text(d.user_count);
                focus.moveToFront();
                focus2.attr("transform", "translate(" + x(d.date) + "," + y(d.user_d_count) + ")");
                focus2.select("text").text(d.user_d_count);
                focus2.moveToFront();
            }

            var area = d3.svg.area()
                        .x(function(d) { return x(d.date); })
                        .y0(height)
                        .y1(function(d) { return y(d.user_count); });

            var area2 = d3.svg.area()
                        .x(function(d) { return x(d.date); })
                        .y0(height)
                        .y1(function(d) { return y(d.user_d_count); });

            svg.append("path")
              .datum(data)
              .attr("class", "area1")
              .attr("d", area)
              ;

            svg.append("path")
              .datum(data)
              .attr("class", "area2")
              .attr("d", area2)
              ;

        });

        $('[data-area-action]').each(function (i, svg) {
            var twidth = this.clientWidth;
            var theight = this.clientHeight;

            var margin = {top: 20, right: 20, bottom: 60, left: 50},
                width = twidth - margin.left - margin.right,
                height = theight - margin.top - margin.bottom;

            var $svg = $(svg);
            var data = JSON.parse(JSON.stringify(graphdata)); //clone

            data.forEach(function(d) {
                d.date = parseDate(d.date.slice(0,10));
                d.a_act = +d.a_act;
                d.u_act = +d.u_act;
            });

            data.sort(function (a, b) {
                return a.date - b.date;
            });

            var x = d3.time.scale()
                .range([0, width]);

            var y = d3.scale.linear()
                .range([height, 0]);
            
            x.domain(d3.extent(data, function(d) { return d.date; }));
            y.domain([0, d3.max(data, function(d) { return ((d.a_act < d.u_act) ? d.u_act : d.a_act); })]);

            var xAxis = d3.svg.axis()
                .scale(x)
                .orient("bottom");

            var yAxis = d3.svg.axis()
                .scale(y)
                .orient("left");

            var bisectDate = d3.bisector(function(d) { return d.date; }).left;

            var svg = d3.select(svg)
                .attr("width", width + margin.left + margin.right)
                .attr("height", height + margin.top + margin.bottom)
                .append("g")
                .attr("transform", "translate(" + margin.left + "," + margin.top + ")");


            svg.append("g")
                .attr("class", "x axis")
                .attr("transform", "translate(0," + height + ")")
                .call(xAxis);

            svg.append("g")
                .attr("class", "y axis")
                .call(yAxis)
                .append("text")
                .attr("transform", "rotate(-90)")
                .attr("y", 6)
                .attr("dy", ".71em")
                .style("text-anchor", "end")
                ;

            var focus = svg.append("g")
              .attr("class", "focus")
              .style("display", "none");

            focus.append("circle")
              .attr("r", 4.5);

            focus.append("text")
              .attr("x", 0)
              .attr("y", -9)
              .attr("dy", ".35em");

            var focus2 = svg.append("g")
              .attr("class", "focus")
              .style("display", "none");

            focus2.append("circle")
              .attr("r", 4.5);

            focus2.append("text")
              .attr("x", 0)
              .attr("y", 9)
              .attr("dy", ".35em");  

            svg.on("mouseover", function() { focus.style("display", null); focus2.style("display", null);})
               .on("mouseout", function() { focus.style("display", "none"); focus2.style("display", "none");})
               .on("mousemove", mousemove);

            function mousemove() {
                var x0 = x.invert(d3.mouse(this)[0]),
                    i = bisectDate(data, x0, 1),
                    d0 = data[i - 1],
                    d1 = data[i];
                if (typeof d1 !== 'undefined'){
                    var d = x0 - d0.date > d1.date - x0 ? d1 : d0;
                } else {
                    var d = d0;
                }
                focus.attr("transform", "translate(" + x(d.date) + "," + y(d.a_act) + ")");
                focus.select("text").text(d.a_act);
                focus.moveToFront();
                focus2.attr("transform", "translate(" + x(d.date) + "," + y(d.u_act) + ")");
                focus2.select("text").text(d.u_act);
                focus2.moveToFront();
            }

            var area = d3.svg.area()
                        .x(function(d) { return x(d.date); })
                        .y0(height)
                        .y1(function(d) { return y(d.a_act); });

            var area2 = d3.svg.area()
                        .x(function(d) { return x(d.date); })
                        .y0(height)
                        .y1(function(d) { return y(d.u_act); });

            svg.append("path")
              .datum(data)
              .attr("class", "area1")
              .attr("d", area)
              ;

            svg.append("path")
              .datum(data)
              .attr("class", "area2")
              .attr("d", area2)
              ;

        });

        $('[data-area-web]').each(function (i, svg) {
            var twidth = this.clientWidth;
            var theight = this.clientHeight;

            var margin = {top: 20, right: 20, bottom: 60, left: 50},
                width = twidth - margin.left - margin.right,
                height = theight - margin.top - margin.bottom;

            var $svg = $(svg);
            var data = JSON.parse(JSON.stringify(graphdata)); //clone

            data.forEach(function(d) {
                d.date = parseDate(d.date.slice(0,10));
                d.u_web = +d.u_web;
                d.a_web = +d.a_web;
            });

            data.sort(function (a, b) {
                return a.date - b.date;
            });

            var x = d3.time.scale()
                .range([0, width]);

            var y = d3.scale.linear()
                .range([height, 0]);
            
            x.domain(d3.extent(data, function(d) { return d.date; }));
            y.domain([0, d3.max(data, function(d) { return ((d.u_web < d.a_web) ? d.a_web : d.u_web); })]);

            var xAxis = d3.svg.axis()
                .scale(x)
                .orient("bottom");

            var yAxis = d3.svg.axis()
                .scale(y)
                .orient("left");

            var svg = d3.select(svg)
                .attr("width", width + margin.left + margin.right)
                .attr("height", height + margin.top + margin.bottom)
                .append("g")
                .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

            svg.append("g")
                .attr("class", "x axis")
                .attr("transform", "translate(0," + height + ")")
                .call(xAxis);

            svg.append("g")
                .attr("class", "y axis")
                .call(yAxis)
                .append("text")
                .attr("transform", "rotate(-90)")
                .attr("y", 6)
                .attr("dy", ".71em")
                .style("text-anchor", "end")
                ;

            var focus = svg.append("g")
              .attr("class", "focus")
              .style("display", "none");

            focus.append("circle")
              .attr("r", 4.5);

            focus.append("text")
              .attr("x", 0)
              .attr("y", -9)
              .attr("dy", ".35em");

            var focus2 = svg.append("g")
              .attr("class", "focus")
              .style("display", "none");

            focus2.append("circle")
              .attr("r", 4.5);

            focus2.append("text")
              .attr("x", 0)
              .attr("y", 9)
              .attr("dy", ".35em");  

            svg.on("mouseover", function() { focus.style("display", null); focus2.style("display", null);})
               .on("mouseout", function() { focus.style("display", "none"); focus2.style("display", "none");})
               .on("mousemove", mousemove);

            var bisectDate = d3.bisector(function(d) { return d.date; }).left;

            function mousemove() {
                var x0 = x.invert(d3.mouse(this)[0]),
                    i = bisectDate(data, x0, 1),
                    d0 = data[i - 1],
                    d1 = data[i];
                if (typeof d1 !== 'undefined'){
                    var d = x0 - d0.date > d1.date - x0 ? d1 : d0;
                } else {
                    var d = d0;
                }
                focus.attr("transform", "translate(" + x(d.date) + "," + y(d.a_web) + ")");
                focus.select("text").text(d.a_web);
                focus.moveToFront();
                focus2.attr("transform", "translate(" + x(d.date) + "," + y(d.u_web) + ")");
                focus2.select("text").text(d.u_web);
                focus2.moveToFront();
            }

            var area = d3.svg.area()
                        .x(function(d) { return x(d.date); })
                        .y0(height)
                        .y1(function(d) { return y(d.a_web); });

            var area2 = d3.svg.area()
                        .x(function(d) { return x(d.date); })
                        .y0(height)
                        .y1(function(d) { return y(d.u_web); });

            svg.append("path")
              .datum(data)
              .attr("class", "area1")
              .attr("d", area)
              ;

            svg.append("path")
              .datum(data)
              .attr("class", "area2")
              .attr("d", area2)
              ;

        });

        $('[data-area-mobile]').each(function (i, svg) {
            var twidth = this.clientWidth;
            var theight = this.clientHeight;

            var margin = {top: 20, right: 20, bottom: 60, left: 50},
                width = twidth - margin.left - margin.right,
                height = theight - margin.top - margin.bottom;

            var $svg = $(svg);
            var data = JSON.parse(JSON.stringify(graphdata));

            data.forEach(function(d) {
                d.date = parseDate(d.date.slice(0,10));
                d.u_mobile = +d.u_mobile;
                d.a_mobile = +d.a_mobile;
            });

            data.sort(function (a, b) {
                return a.date - b.date;
            });

            var x = d3.time.scale()
                .range([0, width]);

            var y = d3.scale.linear()
                .range([height, 0]);
            
            x.domain(d3.extent(data, function(d) { return d.date; }));
            y.domain([0, d3.max(data, function(d) { return ((d.u_mobile < d.a_mobile) ? d.a_mobile : d.u_mobile); })]);

            var xAxis = d3.svg.axis()
                .scale(x)
                .orient("bottom");

            var yAxis = d3.svg.axis()
                .scale(y)
                .orient("left");

            var svg = d3.select(svg)
                .attr("width", width + margin.left + margin.right)
                .attr("height", height + margin.top + margin.bottom)
                .append("g")
                .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

            svg.append("g")
                .attr("class", "x axis")
                .attr("transform", "translate(0," + height + ")")
                .call(xAxis);

            svg.append("g")
                .attr("class", "y axis")
                .call(yAxis)
                .append("text")
                .attr("transform", "rotate(-90)")
                .attr("y", 6)
                .attr("dy", ".71em")
                .style("text-anchor", "end")
                ;

            var focus = svg.append("g")
              .attr("class", "focus")
              .style("display", "none");

            focus.append("circle")
              .attr("r", 4.5);

            focus.append("text")
              .attr("x", 0)
              .attr("y", -9)
              .attr("dy", ".35em");

            var focus2 = svg.append("g")
              .attr("class", "focus")
              .style("display", "none");

            focus2.append("circle")
              .attr("r", 4.5);

            focus2.append("text")
              .attr("x", 0)
              .attr("y", 9)
              .attr("dy", ".35em");  

            svg.on("mouseover", function() { focus.style("display", null); focus2.style("display", null);})
               .on("mouseout", function() { focus.style("display", "none"); focus2.style("display", "none");})
               .on("mousemove", mousemove);

            var bisectDate = d3.bisector(function(d) { return d.date; }).left;

            function mousemove() {
                var x0 = x.invert(d3.mouse(this)[0]),
                    i = bisectDate(data, x0, 1),
                    d0 = data[i - 1],
                    d1 = data[i];
                if (typeof d1 !== 'undefined'){
                    var d = x0 - d0.date > d1.date - x0 ? d1 : d0;
                } else {
                    var d = d0;
                }
                focus.attr("transform", "translate(" + x(d.date) + "," + y(d.a_mobile) + ")");
                focus.select("text").text(d.a_mobile);
                focus.moveToFront();
                focus2.attr("transform", "translate(" + x(d.date) + "," + y(d.u_mobile) + ")");
                focus2.select("text").text(d.u_mobile);
                focus2.moveToFront();
            }

            var area = d3.svg.area()
                        .x(function(d) { return x(d.date); })
                        .y0(height)
                        .y1(function(d) { return y(d.a_mobile); });

            var area2 = d3.svg.area()
                        .x(function(d) { return x(d.date); })
                        .y0(height)
                        .y1(function(d) { return y(d.u_mobile); });

            svg.append("path")
              .datum(data)
              .attr("class", "area1")
              .attr("d", area)
              ;

            svg.append("path")
              .datum(data)
              .attr("class", "area2")
              .attr("d", area2)
              ;

        });

        $('[data-bar-chart]').each(function (i, svg) {
            var twidth = this.clientWidth;
            var theight = this.clientHeight;

            var margin = {top: 20, right: 20, bottom: 60, left: 50},
                width = twidth - margin.left - margin.right,
                height = theight - margin.top - margin.bottom;

            var $svg = $(svg);
            var data = $svg.data('data');

            data.forEach(function(d) {
                d.csemodcount = +d.csemodcount;
                d.average = +d.average;
            });
            data.sort(function (a, b) {
                // sort alphatically
                if (a.name.toLowerCase() < b.name.toLowerCase()) {
                    return -1;
                }
                if (a.name.toLowerCase() > b.name.toLowerCase()) {
                    return 1;
                }
                return 0;
            });

            var x = d3.scale.ordinal()
                .domain(data.map(function (d) {return d.name; }))
                .rangeRoundBands([0, width], 0.05);

            var y = d3.scale.linear()
                .domain([0, d3.max (data, function(d) { return ((d.csemodcount < d.average) ? d.average : d.csemodcount);})])
                .range([height, 0]);
            
            var xAxis = d3.svg.axis()
                .scale(x)
                .orient("bottom");

            var yAxis = d3.svg.axis()
                .scale(y)
                .orient("left");

            var tip = d3.tip()
                .attr('class', 'd3-tip')
                .offset([-10, 0])
                .html(function(d) {
                    return "<span class='m_name'>"+d.name+"</span><br>This course : " + d.csemodcount + "<br>The Average : " + d.average;
                })

            var svg = d3.select(svg)
                .attr("width", width + margin.left + margin.right)
                .attr("height", height + margin.top + margin.bottom)
                .append("g")
                .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

            svg.append("g")
                .attr("class", "x axis")
                .attr("transform", "translate(0," + height + ")")
                .call(xAxis)
                .selectAll("text")
                .style("text-anchor", "end")
                .attr("dx", "-.9em")
                .attr("dy", ".25em")
                .attr("transform", "rotate(-40)");

            svg.append("g")
                .attr("class", "y axis")
                .call(yAxis)
                .append("text")
                .attr("transform", "rotate(-90)")
                .attr("y", 6)
                .attr("dy", ".71em")
                .style("text-anchor", "end")
                ;

            svg.call(tip);                                            

            // Graph Bars
            var sets = svg.selectAll(".set") 
                .data(data) 
                .enter()
                .append("g")
                .attr("class","bar")
                .on('mouseover', tip.show)
                .on('mouseout', tip.hide);

            sets.append("rect")
                .attr("class","bar")
                .attr("width", (width / (data.length * 2))*0.9)
                .attr("x", function(d) { return x(d.name) ; })
                .attr("text-anchor", "middle")
                .attr("height", function(d) { return height - y(d.csemodcount); })
                .attr("y", function(d) { return y(d.csemodcount); })
                ;

            sets.append("rect")
                .attr("class","bar2")
                .attr("width", (width / (data.length * 2))*0.9)
                .attr("x", function(d) { return x(d.name) + x.rangeBand()/2; })
                .attr("text-anchor", "middle")
                .attr("height", function(d) { return height - y(d.average); })
                .attr("y", function(d) { return y(d.average); })
                ;
        });
}