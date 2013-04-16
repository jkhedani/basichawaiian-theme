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
		
		// http://stackoverflow.com/questions/2223305/how-can-i-make-a-function-defined-in-jquery-ready-available-globally
		//window.miniGame = function() {
			// Change the instruction text...
			$('h3.gameInstructions').html('Select the image that correlates with the word below.');
			// Allow user to make a selection
			$(document).on('click','#vocabulary-games a.choiceSelect',function(){
				$(this).parent().siblings().each(function(){ $('a.choiceSelect').removeClass('selected'); });
				$(this).toggleClass('selected');
				$('.gameUserControls a.gameCheck').css('opacity','1.0').css('cursor','pointer');
			});
			
			// When the user makes a selection...
			// NOTE: Event needs to be one(); to prevent multiple firings...
			$(document).on('click','#vocabulary-games a.gameCheck',function() {
				var correctAnswer = $('.gameCard.current .correctAnswer').attr('data-card-id'); // Get current mini game answer...
				var selectedAnswer = $('.gameCard.current a.selected').attr('id'); // Get selected mini game choice...
				var selectedAnswerID = $('.gameCard.current .gameChoices .gameChoice .gameCardControls').attr('data-card-id');

				// Check if choice is correct or wrong...
				if (correctAnswer == selectedAnswer) {
					alert('correct!');
					$('.gameResults .cardsTested').append('<div class="cardTested" data-correct="1" data-wrong="0" data-card-id="'+selectedAnswerID+'"></div>');
					var currentCorrect = parseInt($('.gameResults').attr('data-total-correct'));
					var totalCorrect = currentCorrect + 1;
					$('.gameResults').attr('data-total-correct', totalCorrect);
					//$('body').inc('gameScore', 1);
				} else {
					alert('wrong!');
					$('.gameResults .cardsTested').append('<div class="cardTested" data-correct="0" data-wrong="1" data-card-id="'+selectedAnswerID+'"></div>');
				}

				// Resetting controls
				// Hide the "Check" button...
				$('.gameUserControls a.gameCheck').toggleClass('hidden', 'visible');
				// If we are on the last slide and it is a test card....
				if($('.gameBoard').find('.current').hasClass('last') && $('.gameBoard').find('.current').hasClass('miniGame')) {
					// Show "Finish" button...
					$('.gameUserControls a.gameFinish').toggleClass('hidden', 'visible');
				} else {
					// Show "Next" button...
					$('.gameUserControls a.gameNext').toggleClass('hidden', 'visible');	
				}
			});

		// When Next is clicked...
		// NOTE: .on() is used to fire after ajax returns (similar function to .live())
		$(document).on('click', '#vocabulary-games a.gameNext', function() {

			// Change the instruction text...
			$('h3.gameInstructions').html('Listen and repeat each word you hear until you feel comfortable pronouncing each word.');

			// If we are on a game, let's play!
			if($('.gameBoard').find('.current').next().hasClass('miniGame')) {
				// Change the instruction text...
				$('h3.gameInstructions').html('Select the image that correlates with the word below.');
				// Show the "Check" button...
				$('.gameUserControls a.gameCheck').toggleClass('hidden', 'visible').css('opacity','0.5').css('cursor','default');
				// Hide the "Next" button...
				$('.gameUserControls a.gameNext').toggleClass('hidden', 'visible');
			}

			// Advance the cards...
			// If we are not on the last game object...
			if(!($('.gameBoard').find('.current').hasClass('last'))) {
				$('.gameProgress').find('.current').removeClass('current').next().addClass('current');
				$('.gameBoard').find('.current').removeClass('current').next().addClass('current');
			}

			// If we are on last game object and it is a teach card...
			if($('.gameBoard').find('.current').hasClass('last') && !$('.gameBoard').find('.current').hasClass('miniGame')) {
				//Hide the "Next" button...
				$('.gameUserControls a.gameNext').toggleClass('hidden', 'visible');
				//Show the "Finish" button...
				$('.gameUserControls a.gameFinish').toggleClass('hidden', 'visible');
			}

			// Play audio
      $('#vocabulary-games .gameCard.current').find('audio.pronunciation').get(0).play();
      
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

