    
    var gauges = [];
    var dashContainer;
    var readings = [];	// pretend readings are supplied (named by gauge).
    var i = 0;
    var interv0 = 0;
    var xDim = 0;

    var greenColor = "#107618";
    var yellowColor = "#FFC900";
    var redColor = "#EC4922";
    var darkColor = "#101010";
    var blueColor = "#1030B0";
    var dimBlueColor = "#101560";
    var lightColor = "#EEEEEE";
    var greyColor = "303030";
    var darkGreyColor = "101010";
    var blackColor = "000000";
    var lightBlueColor = "7095F0";

    InitDim();
    interv0 = setInterval(updateGauges, 2000);	// set a basic interval period

    function updateGauges() {
        if (i >= 0) {	// initially use a faster interval and sweep the gauge
            {
                for (var key in gauges) {
                    gauges[key].redraw(i);
                }
                if (i === 0) {
                    clearInterval(interv0);
                    interv0 = setInterval(updateGauges, 75);
                }
                i = i + 5;
                if (i > 100) {
                    i = -1;
                    clearInterval(interv0);
                    interv0 = setInterval(updateGauges, 1000);	// restore a normal interval
                }
            }
        } else {
	        // pass a data array to dashboard.js's UpdateDashboard(values for named gauges)
            for (var key in gauges) {
                /*
		        readings[key] = readings[key] + 10*Math.random()-5;
		        if (readings[key]<0)
			        readings[key] = 0;
		        if (readings[key]>100)
			        readings[key] = 100;
			        */
                console.log(key);
                console.log(readings[key]);
                gauges[key].redraw(readings[key]);
            }
        }
    }

    // code below here could go in a dashboard.js

    function dimChange() {
        dimDash(this.selectedIndex);
        for (var key in gauges) {
            gauges[key].dimDisplay(this.selectedIndex);	// just use the index; could use the indexed entry value.
        }
    }

    function InitDim() {
        var dimOptions = { "Day": 0, "Night": 1 };

        var selectUI = d3.select("#dimmable").append("form").append("select").on("change", dimChange);
        selectUI.selectAll("option").data(d3.keys(dimOptions)).enter().append("option").text(function (d) { return d; });

        selectUI.selectAll("option").data(d3.values(dimOptions)).attr("value", function (d) { return d; });

        var checkOption = function (e) {
            if (e === xDim) { return d3.select(this).attr("selected", "selected"); }
        };

        selectUI.selectAll("option").each(checkOption);
    }

    // some of createGauge is specific to the example (size=120), some belongs in Gauge.
    function createDash(cwidth, cheight)
    {
        this.body = d3.select("#dashboardContainer")
                .append("svg:svg")
                .attr("class", "dash")
                .attr("width", cwidth)//this.config.size)
                .attr("height", cheight);// this.config.size);
        this.body.append("svg:ellipse")	// outer shell
                .attr("cx", cwidth/2)//this.config.cx)
                .attr("cy", cheight/2)//this.config.cy)
                .attr("rx", cwidth/2)//this.config.raduis)
                .attr("ry", (cheight/2)-50)
                .style("fill", xDim<0.5? blueColor : dimBlueColor)
                  .style("fill-opacity", 0.7)
                .style("stroke", "#000")
                .style("stroke-width", "2px");
        var dasharea = this.body.selectAll("ellipse");
        dashContainer =  this.body.append("svg:g").attr("class", "dashContainer")
                .attr("width",cwidth+4)
                .attr("height",cheight+2);
    }

    function dimDash(value) {
            var dasharea =d3.select("#dashboardContainer").selectAll("ellipse");
            dasharea.style("fill",value<0.5 ? blueColor: dimBlueColor);
    }

    function createGauge(myContainer, name, label, sizebias, containerOffsetx, containerOffsety, redZone, yellowZone, greenZone) {
        var config = {
            size: 0 + sizebias,
	    cx: containerOffsetx,
	    cy: containerOffsety,
            label: label,
            minorTicks: 5
        };

        config.redZones = [];	// allows for example upper and lower limit zones
        config.redZones.push(redZone);

        config.yellowZones = [];
        config.yellowZones.push(yellowZone);

        config.greenZones = [];
        config.greenZones.push(greenZone);
        
        gauges[name] = new Gauge(myContainer, name, config);
        gauges[name].render();
	//readings[name] = 50;
    }

    // code from gauge.js, below
    //
    function Gauge(myContainer, name, configuration) {
        this.name = name;
	this.myContainer = myContainer;

        var self = this; // some internal d3 functions do not "like" the "this" keyword, hence setting a local variable

        this.configure = function (configuration) {
            this.config = configuration;

            this.config.size = this.config.size * 0.9;

            this.config.raduis = this.config.size * 0.97 / 2;
            this.config.cx = this.config.cx;// + this.config.size / 4;
            this.config.cy = this.config.cy;// + this.config.size / 2;

            this.config.min = configuration.min || 0;
            this.config.max = configuration.max || 100;
            this.config.range = this.config.max - this.config.min;

            this.config.majorTicks = configuration.majorTicks || 5;
            this.config.minorTicks = configuration.minorTicks || 2;

            this.config.bezelColor = configuration.bezelColor || lightColor;
            this.config.bezelDimColor = configuration.bezelDimColor || greyColor;
            //this.config.dashColor = configuration.dashColor || blueColor; move to Dash
            //this.config.dimDashColor = configuration.dimDashColor || dimBlueColor; move to Dash
            this.config.greenColor = configuration.greenColor || greenColor;
            this.config.yellowColor = configuration.yellowColor || yellowColor;
            this.config.redColor = configuration.redColor || redColor;
            this.config.faceColor = configuration.faceColor || lightColor;
            this.config.dimFaceColor = configuration.dimFaceColor || darkGreyColor;
            this.config.lightColor = configuration.lightColor || "#EEEEEE";
	    this.config.greyColor = configuration.greyColor || "101010";
	    this.config.lightBlueColor = configuration.lightBlueColor || "6085A0";
        };
        
        this.render = function () {
            this.body = this.myContainer//dashContainer//d3.select("#" + this.placeholderName)
                .append("svg:svg")
                .attr("class", "gauge")
                .attr("x", this.myContainer.x)//this.config.cx-this.config.size/4)
                .attr("y", this.myContainer.y)//this.config.cy-this.config.size/4)
                .attr("width", this.myContainer.width)//this.config.size)
                .attr("height", this.myContainer.height)//this.config.size);
  
            this.body.append("svg:circle")	// outer shell
                .attr("cx", this.config.cx)
                .attr("cy", this.config.cy)
                .attr("r", this.config.raduis)
                .style("fill", "#ccc")
                .style("stroke", blackColor )
                .style("stroke-width", "0.5px");

            this.body.append("svg:circle")	// bezel
                .attr("cx", this.config.cx)
                .attr("cy", this.config.cy)
                .attr("r", 0.9 * this.config.raduis)
                .style("fill", (xDim < 0.5 ? this.config.bezelColor : this.config.bezelDimColor))
                .style("stroke", "#e0e0e0")
                .style("stroke-width", "2px");

            var faceContainer = this.body.append("svg:g").attr("class", "faceContainer");	// for day/night changes
            var bandsContainer = this.body.append("svg:g").attr("class", "bandsContainer");	// for day/night changes
            var ticksContainer = this.body.append("svg:g").attr("class", "ticksContainer");	// for day/night changes
            this.redrawDimmableFace(xDim);//0);

            var pointerContainer = this.body.append("svg:g").attr("class", "pointerContainer");
            this.drawPointer(0);
            pointerContainer.append("svg:circle")
                .attr("cx", this.config.cx)
                .attr("cy", this.config.cy)
                .attr("r", 0.12 * this.config.raduis)
                .style("fill", "#4684EE")
                .style("stroke", "#666")
                .style("opacity", 1);
        };

	this.drawBands = function(bandsContainer) { 
            for (var index in this.config.greenZones) {
                this.drawBand(bandsContainer,this.config.greenZones[index].from, this.config.greenZones[index].to, self.config.greenColor);
            }

            for (var index in this.config.yellowZones) {
                this.drawBand(bandsContainer,this.config.yellowZones[index].from, this.config.yellowZones[index].to, self.config.yellowColor);
            }

            for (var index in this.config.redZones) {
                this.drawBand(bandsContainer,this.config.redZones[index].from, this.config.redZones[index].to, self.config.redColor);
            }
	};

        this.redrawDimmableFace = function (value) {
            this.drawFace(value < 0.5 ? self.config.faceColor : self.config.dimFaceColor,	// facecolor
			  value < 0.5 ? self.config.greyColor : lightBlueColor);
        }

        this.drawTicks = function (ticksContainer,color) {

            var fontSize = Math.round(this.config.size / 16);
            var majorDelta = this.config.range / (this.config.majorTicks - 1);
            for (var major = this.config.min; major <= this.config.max; major += majorDelta) {
                var minorDelta = majorDelta / this.config.minorTicks;
                for (var minor = major + minorDelta; minor < Math.min(major + majorDelta, this.config.max); minor += minorDelta) {
                    var minorpoint1 = this.valueToPoint(minor, 0.75);
                    var minorpoint2 = this.valueToPoint(minor, 0.85);

		    ticksContainer.append("svg:line")
                        .attr("x1", minorpoint1.x)
                        .attr("y1", minorpoint1.y)
                        .attr("x2", minorpoint2.x)
                        .attr("y2", minorpoint2.y)
                        .style("stroke", color)
                        .style("stroke-width", "1px");
                }

                var majorpoint1 = this.valueToPoint(major, 0.7);
                var majorpoint2 = this.valueToPoint(major, 0.85);

		ticksContainer.append("svg:line")
                    .attr("x1", majorpoint1.x)
                    .attr("y1", majorpoint1.y)
                    .attr("x2", majorpoint2.x)
                    .attr("y2", majorpoint2.y)
                    .style("stroke", color)
                    .style("stroke-width", "2px");

                if (major == this.config.min || major == this.config.max) {
                    var point = this.valueToPoint(major, 0.63);

		    ticksContainer.append("svg:text")
                        .attr("x", point.x)
                        .attr("y", point.y)
                        .attr("dy", fontSize / 3)
                        .attr("text-anchor", major == this.config.min ? "start" : "end")
                        .text(major)
                        .style("font-size", fontSize + "px")
                        .style("fill", color)
                        .style("stroke-width", "0px");
                }
            }
        };


        this.redraw = function (value) {
            this.drawPointer(value);
        };

        this.dimDisplay = function (value) {
            this.redrawDimmableFace(value);
        };

        this.drawBand = function (bandsContainer, start, end, color) {
            if (0 >= end - start) return;

            bandsContainer.append("svg:path")
                .style("fill", color)
                .attr("d", d3.svg.arc()
                .startAngle(this.valueToRadians(start))
                .endAngle(this.valueToRadians(end))
                .innerRadius(0.70 * this.config.raduis)
                .outerRadius(0.85 * this.config.raduis))
                .attr("transform", function () {
                return "translate(" + self.config.cx + ", " + self.config.cy + ") rotate(270)";
            });
        };

        this.drawFace = function (colorFace,colorTicks) {
            var arc0 = d3.svg.arc()
                .startAngle(0) //this.valueToRadians(0))
                .endAngle(2 * Math.PI)
                .innerRadius(0.00 * this.config.raduis)
                .outerRadius(0.9 * this.config.raduis);

            var faceContainer = this.body.selectAll(".faceContainer");
            var bandsContainer = this.body.selectAll(".bandsContainer");
            var ticksContainer = this.body.selectAll(".ticksContainer");
            var pointerContainer = this.body.selectAll(".pointerContainer");
            var face = faceContainer.selectAll("path");
            if (face == 0)
	    {
                faceContainer
                  .append("svg:path")
                  .attr("d", arc0) //d3.svg.arc()
                  .style("fill", colorFace)
                  .style("fill-opacity", 0.7)
                  .attr("transform",
                      "translate(" + self.config.cx + ", " + self.config.cy + ")");

		this.drawBands(bandsContainer);
	        this.drawTicks(ticksContainer,colorTicks);
                var fontSize = Math.round(this.config.size / 9);
                faceContainer.append("svg:text")
                    .attr("x", this.config.cx)
                    .attr("y", this.config.cy - this.config.size/6 - fontSize / 2 )
                    .attr("dy", fontSize / 2)
                    .attr("text-anchor", "middle")
                    .text(this.config.label)
                    .style("font-size", fontSize + "px")
                    .style("fill", colorTicks)
                    .style("stroke-width", "0px");
	    }
            else
	    {
                face.style("fill", colorFace);
		var facetxt = faceContainer.selectAll("text");
		facetxt.style("fill", colorTicks);
        var ptrtxt = pointerContainer.selectAll("text");
        ptrtxt.style("fill", colorTicks);
		var ticks = ticksContainer.selectAll("line");
		ticks.style("stroke", colorTicks);
		var texts = ticksContainer.selectAll("text");
		texts.style("fill", colorTicks);
        
	    }
	};

        this.drawPointer = function (value) {
            var delta = this.config.range / 13;

            var head = this.valueToPoint(value, 0.85);
            var head1 = this.valueToPoint(value - delta, 0.12);
            var head2 = this.valueToPoint(value + delta, 0.12);

            var tailValue = value - (this.config.range * (1 / (270 / 360)) / 2);
            var tail = this.valueToPoint(tailValue, 0.28);
            var tail1 = this.valueToPoint(tailValue - delta, 0.12);
            var tail2 = this.valueToPoint(tailValue + delta, 0.12);

            var data = [head, head1, tail2, tail, tail1, head2, head];

            var line = d3.svg.line()
                .x(function (d) {
                return d.x;
            })
                .y(function (d) {
                return d.y;
            })
                .interpolate("basis");

            var pointerContainer = this.body.select(".pointerContainer");

            var pointer = pointerContainer.selectAll("path").data([data]);

            pointer.enter()
                .append("svg:path")
                .attr("d", line)
                .style("fill", "#dc3912")
                .style("stroke", "#c63310")
                .style("fill-opacity", 0.7);

            pointer.transition()
                .attr("d", line)
            //.ease("linear")
            .duration(i>=0 ? 50 : 500);

            var fontSize = Math.round(this.config.size / 10);
            pointerContainer.selectAll("text")
                .data([value])
                .text(Math.round(value))
                .enter()
                .append("svg:text")
                .attr("x", this.config.cx)
                .attr("y",  this.config.cy + this.config.size/6 + fontSize)
                .attr("dy", fontSize / 2)
                .attr("text-anchor", "middle")
                .text(Math.round(value))
                .style("font-size", fontSize + "px")
                .style("fill", "#000")
                .style("stroke-width", "0px");
        };

        this.valueToDegrees = function (value) {
            return value / this.config.range * 270 - 45;
        };

        this.valueToRadians = function (value) {
            return this.valueToDegrees(value) * Math.PI / 180;
        };

        this.valueToPoint = function (value, factor) {
            var len = this.config.raduis * factor;
            var inRadians = this.valueToRadians(value);
            var point = {
                x: this.config.cx - len * Math.cos(inRadians),
                y: this.config.cy - len * Math.sin(inRadians)
            };

            return point;
        };

        // initialization
        this.configure(configuration);
    }
