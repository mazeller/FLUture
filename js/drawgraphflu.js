/**
 * @file Consists of functions to sort data, map labels and draw graph for the tools.
 * @author Anugrah Saxena
 */

// orders extracted from database for sorting
// Diag mapping info
var orders = {};
var diag_map = {};

/**
 *  Summary: This function request orders data
 *  The getOrder function makes an ajax call to get orders data for sorting the c3 graph labels and data.
 */
function getOrder(callback) {
	$.ajax({
                url: '/getdata.php',
                type: 'post',
		dataType: 'json',
                data: {'col': "orders"},
                success: function(data, status) {
			callback(data);
                },
                error: function(xhr, desc, err) {
                        console.log(xhr);
                        console.log("Details: " + desc + "\nError:" + err);
                }
        });
}
/**
 * Summary: This function draws the graph for the attributes passed by the tools.
 * 
 * The drawGraphFlu function takes in the attributes passed by the timeseries and correlation PHP files, and the form's id values from the webpage.
 * Then based on the tool type it assigns the values to the variables. Next, it sorts the data to show them with proper order of labels.
 * Finally, it generates the the c3 graph.
 * 
 * @param {matrix} data - Matrix of data for each group consisting of clade frequency of detection corresponding to the xAxis.
 * @param {array} xAxis - Array of values against which data is to be plotted.
 * @param {array} groups - The array of hemagglutinin lineage names or clusters for which the data exists.
 * @param {string} xComponent - The component corresponding to the x-axis values.
 * @param {string} yComponent - The component corresponding to the y-axis values.
 * @param {map} paramMap - This parameter consists of a map of key: value pairs of graph type, normalize, stack and granularity variables
 */
function drawGraphFlu(data, xAxis, groups, xComponent, yComponent, paramMap) {
    // Get the orders from databass if orders are not ready
    if (Object.getOwnPropertyNames(orders).length === 0)
	getOrder(extractOrders);
    // define variables and objects for c3 generator with default values
    var typeData = void 0;
    var typesData = {};
    var axisType = "indexed";
    var xAxisText = void 0;
    var xData = void 0;
    var categoriesXAxis = [];
    var formatTickXAxis = void 0;
    var rotateTickXAxis = 0;
    var fitTickXAxis = !0;
    var labelHeight = 0;
    var translateLabelXComponent;
    var translateLabelYComponent;
    var textLabelXAxis;
    var textLabelYAxis;
    var axisYMin = 0;
    var axisYMax = undefined;
    var axisYPadding = {top:0, bottom:0};
    var linesYGrid = [];
    var patternColor = ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b', '#e377c2', '#7f7f7f', '#bcbd22', '#17becf', '#ffbb78', '#9edae5', '#98df8a', '#aec7e8', '#ff9896', '#c5b0d5', '#c49c94', '#f7b6d2', '#c7c7c7', '#dbdb8d'];

    var graphType = paramMap.get("tool");
    var granularity = (paramMap.has("granularity")) ? paramMap.get("granularity") : "";
    var normalize = paramMap.get("normalize");
    var stack = (paramMap.has("stack")) ? paramMap.get("stack") : false;
    var temp = data.slice();

    if (graphType == "timeseries") {
        // sorting the data
        xAxis.unshift("x");
        //var temp = data.slice();
        temp.unshift(xAxis);
        data = temp;
        // Parameters for plot defined here
        labelHeight = 80;
        axisType = "timeseries";
        xData = 'x';
        formatTickXAxis = '%Y-%m-%d';
        rotateTickXAxis = 60;
        fitTickXAxis = true;
        if (normalize == true) {
	    xAxisText = " Percent";
            axisYMax = 100;
	    for (var tag = 0; tag < groups.length; tag ++) {
	       typesData[groups[tag]] = 'area'; 
	    }
        }
        else {
	    xAxisText = " Count";
	    groups = [];
        }
	translateLabelXComponent = translateLabel(granularity);
        translateLabelYComponent = translateLabel(yComponent);
        textLabelXAxis = 'Time';
        textLabelYAxis = translateLabelYComponent + xAxisText;
    } else if (graphType == "correlation") {
        // sorting the data
        //var temp = data.slice();
        data = temp;
        // Parameters for plot defined here
        axisType = "category";
        typeData = 'bar';
        categoriesXAxis = xAxis;
	
	// dynamic space
	var lengths = categoriesXAxis.map(function(a){return a.length;});
	var longest = Math.max.apply(Math, lengths);
        var arrLen = categoriesXAxis.length;
	var totalLen = longest * arrLen;
	if (totalLen <= 100) {
		labelHeight = 40;
	}
	else if (totalLen <= 250) {
		labelHeight = 50;
	} 
	else if (totalLen <= 350) {
		labelHeight = 60;
	} 
	else if (totalLen <= 450) {
		labelHeight = 70;
	} 
	else {
		labelHeight = 80;
	}


        linesYGrid = [{value: 0}];
        if (normalize == true) {
            xAxisText = " Percent";
            axisYMax = 100;
        } else {
            xAxisText = " Count";
        }
        // turn off groups if unchecked
        if (stack == false) {
            groups = [];
        }
        translateLabelXComponent = translateLabel(xComponent);
        translateLabelYComponent = translateLabel(yComponent);
        textLabelXAxis = translateLabelXComponent;
        textLabelYAxis = translateLabelYComponent + xAxisText;
    }

    // adding the missing sorting logic
    if (yComponent == "sequence_specimen" || yComponent == "site_state" || yComponent == "subtype" || yComponent == "pcr_specimen" || yComponent == "year")
            data.sort();
    if (yComponent == "age_days")
            data.sort(sortAge);
    if (yComponent == "weight_pounds")
            data.sort(sortWeight);
    if (yComponent == "h1_clade" || yComponent == "h3_clade" || yComponent == "ha_clade")
	    data.sort(sortHaClade); 
    if (yComponent == "na_clade")
            data.sort(sortNaClade);
    if (yComponent == "diag_code")
            data.sort(sortDiag);

    // Generate C3 Plot    
    var chat = c3.generate({
	bindto: "#chart",
        data: {
            x: xData,
            columns: data,
            type: typeData,
            types: typesData,
            groups: [groups]
        },
        axis: {
            x: {
                type: axisType,
                categories: categoriesXAxis,
                label: {
                        text: textLabelXAxis,
                        position: 'outer-center',
                },
                tick: {
                    format: formatTickXAxis,
                    rotate: rotateTickXAxis,
                    fit: fitTickXAxis
                },
		//height: function() {return 80;}
		height: labelHeight
            },
            y: {
                mix: axisYMin,
                max: axisYMax,
                label: {
                        text: textLabelYAxis,
                        position: 'middle'
                },
		padding: axisYPadding
            }
        },
        grid: {
            y: {
                lines: linesYGrid,
            }
        },
        color: {
            pattern: patternColor
        },
	tooltip: {
	    contents: function (d, defaultTitleFormat, defaultValueFormat, color) {
		var $$ = this, config = $$.config,
                    titleFormat = config.tooltip_format_title || defaultTitleFormat,
                    nameFormat = config.tooltip_format_name || function (name) { return name; },
                    valueFormat = config.tooltip_format_value || defaultValueFormat,
                    text, i, title, value, name, bgcolor;
		for (i = 0; i < d.length; i++) {
                    if (! (d[i] && (d[i].value || d[i].value === 0))) { continue; }
    
                    if (! text) {
                        title = titleFormat ? titleFormat(d[i].x) : d[i].x;
                        text = "<table class='" + $$.CLASS.tooltip + "'>" + (title || title === 0 ? "<tr><th colspan='3'>" + title + "</th></tr>" : "");
                    }
    
                    name = nameFormat(d[i].name);
                    value = valueFormat(d[i].value, d[i].ratio, d[i].id, d[i].index);
                    bgcolor = $$.levelColor ? $$.levelColor(d[i].value) : color(d[i].id);
    
                    text += "<tr class='" + $$.CLASS.tooltipName + "-" + d[i].id + "'>";
                    text += "<td class='name'><span style='background-color:" + bgcolor + "'></span>" + name + "</td>";
		    if (diag_map.hasOwnProperty(name)) {
		    	text += "<td align='left'>" + diag_map[name] + "</td>";
		    }
                    text += "<td class='value'>" + value + "</td>";
                    text += "</tr>";
                }
		return text + "</table>";
	    }
	},
	/*onrendered: function () {
                d3.select(this.config.bindto).select("svg").attr("height", "550");
                d3.select(this.config.bindto).select(".c3-axis-x-label").attr("dy", "50px");
		d3.select(this.config.bindto).selectAll(".c3-legend-item").attr("transform", "translate(0,15)");
        },*/
    });

    //Update Title
    $("#chartTitle").text(translateLabelYComponent + " per " + translateLabelXComponent);
}

// This function translates the x and y components into meaningful labels
function translateLabel(label)
{
	var transLabel;
        switch (label) {
		case "age_days":
	                transLabel = "Age Group";
			break;
	        case "day":
	                transLabel = "Day of Year";
			break;
                case "week":
                        transLabel = "Week";
                        break;
                case "month":
                        transLabel = "Month";
                        break;
                case "year":
                        transLabel = "Year";
                        break;
	        case "ha_clade":
	                transLabel = "HA Clade Frequency of Detection";
			break;
	        case "h1_clade":
	                transLabel = "H1 Clade Frequency of Detection";
			break;
	        case "h3_clade":
	                transLabel = "H3 Clade Frequency of Detection";
			break;
	        case "na_clade":
	                transLabel = "NA Clade Frequency of Detection";
			break;
	        case "pcr_specimen":
	                transLabel = "Specimen used for PCR";
			break;
	        case "sequence_specimen":
	                transLabel = "Specimen used for Sequencing";
			break;
	        case "site_state":
	                transLabel = "Pig's Origin State";
			break;
	        case "subtype":
	                transLabel = "Subtype";
			break;
	        case "testing_facility":
	                transLabel = "Source of Data";
			break;
                case "weight_pounds":
                        transLabel = "Weight(lbs)";
                        break;
		case "diag_code":
			transLabel = "Bacterial Coinfection";
			break;
		default:
			transLabel = label;
	}
        return transLabel;
}

//Numerical sorting
function sortNumber(a,b) {
    return a - b;
}

//Age sorting
function sortAge(a,b) {
	if (a instanceof HTMLOptionElement) {
		a = a.value;
		b = b.value;
	} else {
		a = a[0];
		b = b[0];
	}
        aVal = ageToNumber(a);
        bVal = ageToNumber(b);
        return aVal - bVal;
}

//Weight sorting
function sortWeight(a,b) {
	if (a instanceof Array) {
		a = a[0];
		b = b[0];
	} else if (a instanceof HTMLOptionElement) {
		a = a.value;
		b = b.value;
	}
        aVal = weightToNumber(a);
        bVal = weightToNumber(b);
        return aVal - bVal;
}

//Ha clade sort
function sortHaClade(a,b) {
        if (a instanceof Array) {
                a = a[0];
                b = b[0];
        } else if (a instanceof HTMLOptionElement) {
		a = a.value;
		b = b.value;
	}
	aVal = orders["ha_clade"].indexOf(a);
        bVal = orders["ha_clade"].indexOf(b);
        return aVal - bVal;
}

//Na clade sort
function sortNaClade(a,b) {
        if (a instanceof Array) {
                a = a[0];
                b = b[0];
        }else if (a instanceof HTMLOptionElement) {
		a = a.value;
		b = b.value;
	}
        aVal = orders["na_clade"].indexOf(a);
        bVal = orders["na_clade"].indexOf(b);
        return aVal - bVal;
}

//diag sort
function sortDiag(a,b) {
        if (a instanceof Array) {
                a = a[0];
                b = b[0];
        } 
	aVal = orders["diag_code"].indexOf(a);
	bVal = orders["diag_code"].indexOf(b);
        return aVal - bVal;
}



// This function converts the swine age label to corresponding number
function ageToNumber(ageString) {
	var age;
        switch (ageString) {
		case "neonate":
                	age = 0;
			break;
	        case "suckling":
	                age = 1;
			break;
	        case "nursery":
	                age = 2;
			break;
	        case "grow finisher":
	                age = 3;
			break;
	        case "adult":
	                age = 4;
			break;
		default:
			age = -1;
	}
        return age;
}

// This function converts the swine weight label to corresponding number. Author: Siying Lyu
function weightToNumber(weightString) {
	var weight;
        switch (weightString) {
                case "Under 50":
                        weight = 0;
                        break;
                case "50\-100":
                        weight = 1;
                        break;
                case "100\-150":
                        weight = 2;
                        break;
                case "150\-200":
                        weight = 3;
                        break;
                case "200\-250":
                        weight = 4;
                        break;
                case "250\-300":
                        weight = 5;
                        break;
                case "300\-350":
                        weight = 6;
                        break;
                case "350\-400":
                        weight = 7;
                        break;
                case "400\-450":
                        weight = 8;
                        break;
                case "450\-500":
                        weight = 9;
                        break;
                case "Above 500":
                        weight = 10;
                        break;
                default:
                        weight = -1;
        }
        return weight;
}
//Find unique values
function uniqueValues(dataObject, field)
{
        var result = [];
        for (var key in dataObject) {
                if (result.indexOf(dataObject[key][field]) == -1) {
                        result.push(dataObject[key][field]);
                }
        }
        return result;
}
//Get orders
function extractOrders(rOrders) {
        orders.ha_clade = rOrders.ha_clade;
        orders.na_clade = rOrders.na_clade;
        orders.diag_code = [];

        if(rOrders.hasOwnProperty("diag_info")) {
                var keys = Object.keys(rOrders.diag_info);
                keys.forEach(function(key) {
                        diag_map[rOrders.diag_info[key].diag_code] = rOrders.diag_info[key].diag_text;
                        orders.diag_code.push(rOrders.diag_info[key].diag_code);
                });
        }

}
