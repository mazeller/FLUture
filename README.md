# ISU <i>FLU</i>ture
Source Code for the ISU FLUture website

<h2>Pages</h2>
<h3>index.php</h3>
Landing page with instructions and explanation of the website. Additionally has a C3 chart component that shows the number of cases per year to date, with a feature at the bottom that shows the date recieved of case on the last row of the database.
<h3>correlation.php</h3>
A C3 bar chart that is used to display any binned database varriable against any other database vairable.
<h3>timeseries.php</h3>
A C3 timeseries chart used to show any variable in the database plotted against time, using recieved date as the value for the x-Axis.
<h3>regional.php</h3>
An implementation of the US-map library that relies on raphael to show where case data originated from over time. There are future plans for this functionality to be extended to plot other variables geographically.
<h3>heatmap.php</h3>
A script that renders tables based on count data for ha clades and na clades where where applicable.
<h3>3dview.php</h3>
An unreleased component that uses PV protein viewer to render HA for visual structural analysis.


<h2>JavaScript Libraries</h2>
<h3>bio-pv</h3>
Part of the PV JavaScript protein viewer. Used in view3d.php, an unreleased component.
<h3>D3</h3>
Data Driven Document's libray, required by C3.
<h3>C3</h3>
D3-based reusable chart library. Used to display the correlation and timeseries graphs.
<h3>dataloader.js</h3>
In-house script used to handle pulling and processing of data from underlying database.
<h3>jQRangeSlider</h3>
Library that draws date sliders using jQuery and jQuery UI.
<h3>jQuery</h3>
Used to simplify handling events on pages.
<h3>jQueryUI</h3>
Used to extend jQuery functionality. Requirement for jQRangeSlider.
<h3>Raphael</h3>
Javascript library for working with vector graphics. Used to draw the map in regional.php.

<h2>Theme Related Files</h2>
The following files are part of the Iowa State University stock theme, available from sample.iastate.edu

<h3>favicon.ico</h3>
<h3>autoload.php</h3>
<h3>/IastateTheme/*</h3>
<h3>/img/*</h3>
<h3>/css/*</h3>

