/**
 * @file Consists of functions to sort data, map labels and draw graph for the tools.
 * @author Anugrah Saxena
 */

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
    var patternColor = ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b', '#e377c2', '#7f7f7f', '#bcbd22', '#17becf', '#9edae5', '#aec7e8', '#ffbb78', '#98df8a', '#ff9896', '#c5b0d5', '#c49c94', '#f7b6d2', '#c7c7c7', '#dbdb8d'];

    var graphType = paramMap.get("tool");
    var granularity = (paramMap.has("granularity")) ? paramMap.get("granularity") : "";
    var normalize = paramMap.get("normalize");
    var stack = (paramMap.has("stack")) ? paramMap.get("stack") : false;

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
	translateLabelXComponent = granularity;
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
    if (yComponent == "weight_pounds")
            data.sort(sortWeight);
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
                case "weight_pounds":
                        transLabel = "Weight(lbs)";
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

//Weight sorting. Author: Siying Lyu
function sortWeight(a,b) {
	if (a instanceof Array) {
		aVal = weightToNumber(a[0]);
		bVal = weightToNumber(b[0]);
	} else {
        	aVal = weightToNumber(a);
        	bVal = weightToNumber(b);
	}
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
                case "gamma-npdm-like":
                        clade = 5;
                        break;
	        case "gamma2-beta-like":
	                clade = 6;
			break;
	        case "delta1":
			clade = 7;
			break;
        	case "delta1a":
        	        clade = 8;
			break;
        	case "delta1b":
        	        clade = 9;
			break;
        	case "delta2":
        	        clade = 10;
			break;
        	case "delta-like":
        	        clade = 11;
			break;
        	case "pdmH1":
        	        clade = 12;
			break;
                case "H4":
                        clade = 13;
                        break;
                case "cluster_I":
                        clade = 14;
                        break;
        	case "cluster_IV":
			clade = 15;
			break;
        	case "cluster_IVA":
        	        clade = 16;
			break;
	        case "cluster_IVB":
	                clade = 17;
			break;
	        case "cluster_IVC":
	                clade = 18;
			break;
	        case "cluster_IVD":
	                clade = 19;
			break;
	        case "cluster_IVE":
	                clade = 20;
			break;
	        case "cluster_IVF":
	                clade = 21;
			break;
	        case "2010.1":
	                clade = 22;
			break;
	        case "2010.2":
	                clade = 23;
			break;
                case "human-to-swine-2013":
                        clade = 24;
                        break;
	        case "human-to-swine-2016":
	                clade = 25;
			break;
	        case "human-to-swine-2017":
	                clade = 26;
			break;
                case "human-to-swine-2018":
                        clade = 27;
                        break;
                case "Other":
                        clade = 28;
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

// This function converts the swine weight label to corresponding number. Author: Siying Lyu
function weightToNumber(weightString) {
        switch (weightString) {
                case "Under\t50":
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
                case "Above\t500":
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
