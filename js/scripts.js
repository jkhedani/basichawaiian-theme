jQuery(document).ready(function($){
	/**
	 *	Chart.js
	 *	http://www.chartjs.org/docs/
	 *	Note: Be sure to enable IE support via explorerCanvas
	 *	https://code.google.com/p/explorercanvas/
	 */

	 // Only run on statistics page...
	 if ( $('body').hasClass('page-template-statistics-php') ) {
	 	// Testing chart data... 
		var myFirstChartData = [
			{ value : studentsCompletedModuleOne, color: "#949FB1" },
			{ value : activeStudents, color: "#4D5360" },
			{ value : studentsNotParticipating, color: "#F7464A" },
			
			{ value : 20, color: "#E2EAE9" },
			{ value : 50, color: "#D4CCC5" },
			
			

		];
		var ctx = $("#canvas-chart").get(0).getContext("2d");
		var myFirstChart 		 = new Chart(ctx).Doughnut( myFirstChartData );
	}

		
});

