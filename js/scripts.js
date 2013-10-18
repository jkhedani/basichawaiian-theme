jQuery(document).ready(function($){
	/**
	 *	Chart.js
	 *	http://www.chartjs.org/docs/
	 *	Note: Be sure to enable IE support via explorerCanvas
	 *	https://code.google.com/p/explorercanvas/
	 */

	 // Testing chart data... 
		var myFirstChartData = [
			{ value : activeStudents, color: "#4D5360" },
			{ value : studentsNotParticipating, color: "#F7464A" },
			{ value : 20, color: "#E2EAE9" },
			{ value : 50, color: "#D4CCC5" },
			{ value : 90, color: "#949FB1" },
			

		];
		var ctx = $("#canvas-chart").get(0).getContext("2d");
		var myFirstChart 		 = new Chart(ctx).Doughnut( myFirstChartData );

		
});

