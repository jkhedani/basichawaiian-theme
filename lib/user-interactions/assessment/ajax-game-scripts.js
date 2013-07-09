jQuery(document).ready(function($) {

/**
 *  General Lesson Scripts
 */
if ( $('body').hasClass('single-topics') ) { // Only run on "Topic" pages.
  // "Start a lesson" / "Prompt lesson modal"
  $('a.prompt-lesson-start').on('click', function() {
    $('#lesson-start-modal').modal();
    var lessonTitle = $(this).find('h4').text(); // Get destination title
    var lessonURL = $(this).data('lesson-url'); // Get destination URL
    $('#lesson-start-modal').find('.modal-header h3').html(lessonTitle); // Replace modal title with destination title
    $('#lesson-start-modal').find('.modal-footer a.start-lesson').attr( 'href', lessonURL ); // Update modal start destination URL
  });

  // "Abort a lesson"
  $('a.abort-lesson').on( 'click', function() {
    $('#lesson-start-modal').modal('hide');
  });
}

// "Finish Lesson"
$('a.finish-lesson').on( 'click',function() {
  var lesson_id = $('.lesson-container').data('lesson-id');
  var lesson_outcome = $(this).data('lesson-outcome');
  var currency_type_id = $(this).data('currency-type-id');
  var landing_id = $(this).data('landing-id');
  var lesson_results = []; // Put together outcome of lesson for scoring
  if ( $('.lesson-card.test-card').data('lesson-object-id') ) {
    $('.lesson-card.test-card').each(function(){
      var lessonObjectID = $(this).data('lesson-object-id');
      var lessonObjectResult = $(this).data('lesson-object-result');
      var tempString = lessonObjectID+","+lessonObjectResult;
      lesson_results.push(tempString);
    });
  }
  $('.lesson-container').hide('slow');
  $.post(ajax_scripts.ajaxurl, {
    action: 'finish_lesson',
    nonce: ajax_scripts.nonce,
    lessonID: lesson_id,
    lessonOutcome: lesson_outcome,
    currencyTypeID: currency_type_id,
    lessonResults: lesson_results,
    landingID: landing_id,
  }, function(response) {
    if ( response.success === true ) {
      $('.lesson-container').show('slow');
      $('.lesson-container').html(response.html);
    }
  });
});

/**
 *  General Lesson Assessment Scripts
 */

function updateScore(result) {
  if ( result == 'correct' ) {
    $('.lesson-card.current').data('lesson-object-result', 1); 
  } else if ( result == 'incorrect' ) {
    $('.lesson-card.current').data('lesson-object-result', 0);
  }
}

function updateKarma() {
  $('.lesson-karma .karma-point:first-child').hide( 'slow', function() {
    $(this).remove();
    // If no karma points exist, end game.
    if ( $('.karma-point').parent().length < 1 ) {
      $('.finish-lesson').data('lesson-outcome','fail');
      $('.finish-lesson').click();
    }
  });
}

function showFeedback(result) {
  if ( result == 'correct' ) {
    $('.lesson-feedback').addClass('alert-success'); // Make feedback 'green'
    $('.lesson-feedback .lesson-feedback-correct').show(); // Show correct answer message
  } else if ( result == 'incorrect' ) {
    $('.lesson-feedback').addClass('alert-error'); // Make feedback 'red'
    $('.lesson-feedback .lesson-feedback-incorrect').show(); // Show "incorrect"
  }
  $('.lesson-feedback').show();
}

function hideFeedback() {
  $('.lesson-feedback').removeClass('alert-success');
  $('.lesson-feedback').removeClass('alert-error');
  $('.lesson-feedback, .lesson-feedback .lesson-feedback-correct, .lesson-feedback .lesson-feedback-incorrect').hide();
}

function updateProgressBar(result) {
  if ( result == 'correct' ) {
    $('.lesson-header').find('.bar.current').removeClass('bar-info').addClass('bar-success');
  } else if ( result == 'incorrect' ) {
    $('.lesson-header').find('.bar.current').removeClass('bar-info').addClass('bar-danger');
  }
}

function advanceProgressBar() {
  if ( $('.lesson-header').find('.bar.current').hasClass('last') ) {
    // do nothing
  } else {
    $('.lesson-header').find('.bar.current').removeClass('current').next().addClass('current');
  }
}

function showLessonControls() {
  if ( $('.lesson-content').find('.lesson-card.current').hasClass('last') ) {
    $('.finish-lesson').show();
  } else {
    $('.advance-lesson').show();
  }
}

function disableLessonChoices() {
  $('.lesson-card-assessment-option').attr('disabled', true);
  $('.lesson-card-assessment-option').on('click',function(e) {
    e.preventDefault();
  });
}

    // If user get's vocab wrong, display the correct answer for them. (currently in english)
    // var correctAnswer = $(this).parent().find('.correct-option img').attr('alt');
    // alert(correctAnswer);
    // $('.lesson-feedback .lesson-feedback-incorrect .lesson-feedback-correct-option').html(correctAnswer);


var choiceHandler = function(e) {
  // Display appropriate feedback
  var correctAnswer = $(this).parent().find('.correct-option').text();
  if ()
  // If choice is correct
  if ( $(this).hasClass('correct-option') ) { 
    updateScore('correct');
    showFeedback('correct');  
    updateProgressBar('correct');
  // If choice is incorrect
  } else { 
    updateScore('incorrect');
    showFeedback('incorrect');
    updateProgressBar('incorrect');
    updateKarma();
    if ( $('body').hasClass('single-phrases_lessons') ) {
      $('.lesson-feedback .lesson-feedback-incorrect .lesson-feedback-correct-option').html(correctAnswer);
    }
  }
  // Show proper lesson controls
  showLessonControls();
  // Disable choice options after click
  $('.lesson-card-assessment-option').attr('disabled', true).unbind('click');
}

// Student selects choice
$('.lesson-card-assessment-option').on( 'click', choiceHandler );

var lessonAdvanceHandler = function() {
  // Show next lesson card
  $('.lesson-content').find('.lesson-card.current').removeClass('current').next().addClass('current');
  // Advance progress bar
  advanceProgressBar();
  // Reset feedback and controls for next assessment
  hideFeedback();
  $('.finish-lesson, .advance-lesson').hide();
   // Re-enable click for choices
  $('.lesson-card-assessment-option').attr('disabled', false);
  $('.lesson-card-assessment-option').bind('click', choiceHandler);
}

// Student advances lesson
$('.advance-lesson').on( 'click', lessonAdvanceHandler);




/**
 *  Vocabulary Lesson Scripts
 */
if ( $('body').hasClass('single-vocabulary_lessons') ) { // Only run on "Vocabulary Lesson" pages.
  
  function playCurrentLearnCard() {
    // For vocabulary lessons, after the audio file has finished playing...
    if ( $('.lesson-card.current').hasClass('test-card') ) {
      $('.advance-lesson').hide();
    } else {
      $('.advance-lesson').show();
    }
    $('.lesson-card.current').find('audio.pronunciation').get(0).play();
    $('.lesson-card.current').find('audio.pronunciation').on('ended',function() { 
      // callback for audio if you need
    });  
  }

  function showInstructions() {
    if ( $('.lesson-card.current').next().hasClass('learn-card') ) {
      $('.lesson-instructions.learn-instructions').show();
      $('.lesson-instructions.test-instructions').hide();
    } else {
      $('.lesson-instructions.learn-instructions').hide();
      $('.lesson-instructions.test-instructions').show();
    }
  }

  // On vocab start, show proper instructions
  showInstructions();

  // Then, play the first audio clip.
  playCurrentLearnCard();

  // Modify .advance-lesson functionality
  $('.advance-lesson').unbind('click');
  var lessonAdvanceHandler = function() {

    if ( $('.lesson-card.current').hasClass('learn-card') ) {
      updateProgressBar('correct'); // automatically mark vocabulary progress as correct
    }
    
    hideFeedback();
    advanceProgressBar();
    showInstructions();
     // Show next lesson card
    $('.lesson-content').find('.lesson-card.current').removeClass('current').next().addClass('current');
    playCurrentLearnCard();
    
    $('.lesson-card-assessment-option').attr('disabled', false);
    $('.lesson-card-assessment-option').bind('click', choiceHandler);

  }
  $('.advance-lesson').on( 'click', lessonAdvanceHandler );

  // When audio playback button is played, trigger said game card's audio element
  $(document).on('click', '.play-pronunciation', function() {
    $(this).parent().find('audio.pronunciation').get(0).play();
  });

  /**
   * Show Translation
   */
  var translationHandler = function() {
    $(this).parent().find('.translation').toggleClass('hidden', 'visible');
    if ( $(this).parent().find('.translation').is(':visible') ) {
      $(this).find('span').html('Hide');
    } else {
      $(this).find('span').html('Show');
    }
  }
  $(document).on('click', '.show-translation', translationHandler);


}

/**
 * Unit Page Scripts (Kukui People)
 */
if ( $('body').hasClass('single-units') ) {
  // Module List Carousel
  $('.carousel').carousel({
    'interval' : false,
  });
}

}); // end jQuery
