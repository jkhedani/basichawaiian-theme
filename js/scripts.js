jQuery(document).ready(function($){

	// Modify Data Values Plug-in
	// http://stackoverflow.com/questions/5656640/increment-decrement-data-in-jquery/5656660#5656660
	$.fn.inc = function(prop, val) {
    return this.each(function() {
        var data = $(this).data();
        if(!(prop in data)) {
            data[prop] = 0;
        }
        data[prop] += val;
    });
  }

	// Vocabulary Game Controllers
	if($('#vocabulary-games').length > 0) {
		// Set a new game score for the user...
		$('body').data('gameScore', 0);
		// When Next is clicked...
		// NOTE: .on() is used to fire after ajax returns (similar function to .live())
		$(document).on('click', '#vocabulary-games a.gameSubmit', function() {
			// Change the instruction text...
			$('h3.gameInstructions').html('Listen and repeat each word you hear until you feel comfortable pronouncing each word.');

			//If we are on a mini game...
			if($('.gameProgress').find('.current').next().hasClass('miniGame')) {
				// Change the instruction text...
				$('h3.gameInstructions').html('Select the image that correlates with the word below.');
				// Show the "Check" button...
				$('.gameBoard a.gameCheck').toggleClass('hidden', 'visible').css('opacity','0.5').css('cursor','default');
				// Hide the "Submit" button...
				$('.gameBoard a.gameSubmit').toggleClass('hidden', 'visible');

				// Allow user to make a selection
				$(document).on('click','#vocabulary-games a.choiceSelect',function(){
					$(this).parent().siblings().each(function(){ $('a.choiceSelect').removeClass('selected'); });
					$(this).toggleClass('selected');
					$('.gameBoard a.gameCheck').css('opacity','1.0').css('cursor','pointer');
				});
				
				// When the user makes a selection...
				// NOTE: Event needs to be one(); to prevent multiple firings...
				$(document).one('click','#vocabulary-games a.gameCheck',function(){
					var correctAnswer = $('.gameCard.current .correctAnswer').html(); // Get current mini game answer...
					var selectedAnswer = $('.gameCard.current a.selected').attr('id'); // Get selected mini game choice...
					
					// Check if choice is correct or wrong...
					if (correctAnswer == selectedAnswer) {
						alert('correct!');
						$('body').inc('gameScore', 1);
					} else {
						alert('wrong!');
					}

					// Hide the "Check" button...
					$('.gameBoard a.gameCheck').toggleClass('hidden', 'visible');
					// Show the "Submit" button...
					$('.gameBoard a.gameSubmit').toggleClass('hidden', 'visible');
				});
			}

			// If we are not on the last game object...
			if(!($('.gameProgress').find('.current').hasClass('last'))) {
				$('.gameProgress').find('.current').removeClass('current').next().addClass('current');
				$('.gameBoard').find('.current').removeClass('current').next().addClass('current');
			}

			// If we are on last game object...
			if($('.gameProgress').find('.current').hasClass('last')) {
				var numberOfMiniGames = $('.gameProgress .miniGame').length;
				var numberCorrect = $('body').data('gameScore');
				// .each(function(){
				// 	numberOfMiniGames + 1;
				// });
				$('.gameResults').html('You scored '+numberCorrect+' out of '+numberOfMiniGames);
				// Hide the "Submit" button...
				$('.gameBoard a.gameSubmit').toggleClass('hidden', 'visible');
				// Show the "Finish" button...
				$('.gameBoard a.gameFinish').toggleClass('hidden', 'visible');
			}

			//setTimeout(function() {
        $('#vocabulary-games .gameCard.current').find('audio.pronunciation').get(0).play();
      //}, 1000);
		});
		
		$(document).on('click', '#vocabulary-games a.gameFinish', function() {
			// Go To Last Slide
			$('.gameProgress').find('.current').removeClass('current').next().addClass('current');
			$('.gameBoard').find('.current').removeClass('current').next().addClass('current');
			// Hide the Finish button
			$('.gameBoard a.gameFinish').toggleClass('hidden', 'visible');
			// Show the Continue button
			$('.gameBoard a.gameContinue').toggleClass('hidden', 'visible');
			// Show the Restart button
			$('.gameBoard a.gameRestart').toggleClass('hidden', 'visible');
		});

		// When audio playback button is played, trigger said game card's audio element
		$(document).on('click', '#vocabulary-games a.pronunciationPlay', function() {
			$(this).parent().find('audio.pronunciation').get(0).play();
		});

		// When "Show Hawaiian" is clicked, show
		$(document).on('click', '#vocabulary-games a.toggleHwnTranslation', function() {
			$(this).parent().find('.hwnTranslation').toggleClass('hidden', 'visible');
		});

		// When "Show English" is clicked, show
		$(document).on('click', '#vocabulary-games a.toggleEngTranslation', function() {
			$(this).parent().find('.engTranslation').toggleClass('hidden', 'visible');
		});

	} // vocabularyGames

});