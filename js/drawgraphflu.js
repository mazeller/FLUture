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
 * @param {striong} graphType - This parameter is used to differentiate which tool  made the function call.
 * @param {boolean} normalize - This parameter is part of the webtools. When set, it shows the graph based on percentage rather then count.
 * @param {boolean} [stack=false] - Optional parameter with default value false. It is part of the correlation tool webform.
 */
function drawGraphFlu(data, xAxis, groups, xComponent, yComponent, graphType, normalize, stack=false) {
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
    var translateLabelXComponent;
    var translateLabelYComponent;
    var textLabelXAxis;
    var textLabelYAxis;
    var linesYGrid = [];
    var patternColor = ['#1f77b4', '#aec7e8', '#ff7f0e', '#ffbb78', '#2ca02c', '#98df8a', '#d62728', '#ff9896', '#9467bd', '#c5b0d5', '#8c564b', '#c49c94', '#e377c2', '#f7b6d2', '#7f7f7f', '#c7c7c7', '#bcbd22', '#dbdb8d', '#17becf', '#9edae5'];

    if (graphType == "timeseries") {
        // sorting the data
        xAxis.unshift("x");
        var temp = data.slice();
        temp.unshift(xAxis);
        data = temp;
        // Parameters for plot defined here
        axisType = "timeseries";
        xData = 'x';
        formatTickXAxis = '%Y-%m-%d';
        rotateTickXAxis = 60;
        fitTickXAxis = true;
        if (normalize == true) {
	    xAxisText = " Percent";
	    for (tag in groups) {
	       typesData[groups[tag]] = 'area'; 
	    }
        }
        else {
	    xAxisText = " Count";
	    groups = [];
        }
        translateLabelXComponent = translateLabel(xComponent);
        translateLabelYComponent = translateLabel(yComponent);
        textLabelXAxis = 'Time';
        textLabelYAxis = translateLabelYComponent + xAxisText;
    } else if (graphType == "correlation") {
        // sorting the data
        var temp = data.slice();
        data = temp;
        // Parameters for plot defined here
        axisType = "category";
        typeData = 'bar';
        categoriesXAxis = xAxis;
        linesYGrid = [{value: 0}];
        if (normalize == true) {
            xAxisText = " Percent";
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
    if (yComponent == "sequence_specimen" || yComponent == "site_state" || yComponent == "subtype" || yComponent == "pcr_specimen")
            data.sort();
    if (yComponent == "age_days")
            data.sort(sortAge);
    if (yComponent == "h1_clade" || yComponent == "h3_clade" || yComponent == "ha_clade" || yComponent == "na_clade")
	    data.sort(sortClade); 

    // Generate C3 Plot    
    var chat = c3.generate({
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
                }
            },
            y: {
                label: {
                        text: textLabelYAxis,
                        position: 'middle'
                }
            }
        },
        grid: {
            y: {
                lines: linesYGrid
            }
        },
        color: {
            pattern: patternColor
        }
    });

    //Update Title
    $("#chartTitle").text(translateLabelYComponent + " per " + translateLabelXComponent);
}

// This function translates the x and y components into meaningful labels
function translateLabel(label)
{
        switch (label) {
		case "age_days":
	                transLabel = "Age Group";
			break;
	        case "day":
	                transLabel = "Day of Year";
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
        aVal = ageToNumber(a[0]);
        bVal = ageToNumber(b[0]);
        return aVal - bVal;
}

//H1 clade sort
function sortClade(a,b) {
        aVal = cladeToNumber(a[0]);
        bVal = cladeToNumber(b[0]);
        return aVal - bVal;
}

// This function converts the clade string to a corresponding number that is used to sort the labels as per the Latin symbol order
function cladeToNumber(cladeString) {
        switch (cladeString) {
	        case "alpha":
	                clade = 0;
			break;
	        case "beta":
	                clade = 1;
			break;
	        case "gamma":
	                clade = 2;
			break;
	        case "gamma2":
	                clade = 3;
			break;
	        case "gamma-like":
	                clade = 4;
			break;
	        case "gamma2-beta-like":
	                clade = 5;
			break;
	        case "delta1":
			clade = 6;
			break;
        	case "delta1a":
        	        clade = 7;
			break;
        	case "delta1b":
        	        clade = 8;
			break;
        	case "delta2":
        	        clade = 9;
			break;
        	case "delta-like":
        	        clade = 10;
			break;
        	case "pdmH1":
        	        clade = 11;
			break;
        	case "cluster_IV":
			clade = 12;
			break;
        	case "cluster_IVA":
        	        clade = 13;
			break;
	        case "cluster_IVB":
	                clade = 14;
			break;
	        case "cluster_IVC":
	                clade = 15;
			break;
	        case "cluster_IVD":
	                clade = 16;
			break;
	        case "cluster_IVE":
	                clade = 17;
			break;
	        case "cluster_IVF":
	                clade = 18;
			break;
	        case "2010.1":
	                clade = 19;
			break;
	        case "2010.2":
	                clade = 20;
			break;
	        case "human-to-swine-2016":
	                clade = 21;
			break;
	        case "human-to-swine-2017":
	                clade = 22;
			break;
		default:
			clade = -1;
	}
        return clade;
}

// This function converts the swine age label to corresponding number
function ageToNumber(ageString) {
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
