jQuery(document).ready(function($) {

// REMOVE AFTER TESTING IS COMPLETE !!!!!!
// VOCABULARY GAME: Resets user records for vocabulary game.
// NOTE: .on() is used to fire after ajax returns (similar function to .live())
$(document).on('click', '.reset-scores', function() {
  // post data to function
  $.post(ajax_scripts.ajaxurl, {
      dataType: "jsonp",
      action: 'reset_scores',
      nonce: ajax_scripts.nonce,
    }, function(response) {
      if (response.success===true) {
        alert('Your database record has been reset!');
      } else {
        // Bad Response message
      }
  });
  return false;
}); // end click
// REMOVE AFTER TESTING IS COMPLETE !!!!!!

/**
 *  Global
 */
// http://stackoverflow.com/questions/439463/how-to-get-get-and-post-variables-with-jquery
var $_GET = {};
document.location.search.replace(/\??(?:([^=]+)=([^&]*)&?)/g, function () {
  function decode(s) {
      return decodeURIComponent(s.split("+").join(" "));
  }
  $_GET[decode(arguments[1])] = decode(arguments[2]);
});

/**
 *
 * Home Page Scripts
 * Scripts that run only on the home page. These can be anywhere from interface methods visibility methods.
 *
 */
if ( $('body').hasClass('home') && $('body').hasClass('logged-in') ) {
  // Create popover to provide access to content
  $('ul.units li.unit a.dashboard-selection').popover({
    'placement' : 'right',
    'html' : true,
  });
  // When selecting a unit...
  $('ul.units li.unit a.dashboard-selection').on('click',function() {
    // Animate avatar
    $('body').find('.user-avatar.male').toggleClass('wave','default');
    $('body').find('.user-avatar.female').toggleClass('wave','default');
  });
}

/**
 *
 * Unit Page Scripts (Kukui People)
 * Scripts that run only on the unit page. These can be anywhere from interface methods visibility methods.
 *
 */
if ( $('body').hasClass('single-units') ) {
  // Module List Carousel
  $('.carousel').carousel({
    'interval' : false,
  });
  // Send carousel to correct position
  if ( $_GET["module"] ) {
    var modulePosition = parseInt($_GET["module"]);
    $('.carousel').carousel(modulePosition);
  }
  // Move Carousel position indicator
  $(document).on('click', '.carousel-indicator-control.left', function() {
    if ( $('body .carousel-indicators li.active').hasClass('first') ) {
      $('body').find('.carousel-indicators li.active').removeClass('active');
      $('body').find('.carousel-indicators li.last').addClass('active');
    } else {
      $('body').find('.carousel-indicators li.active').removeClass('active').prev().addClass('active');
    }
  });

  $(document).on('click', '.carousel-indicator-control.right', function() {
    if ( $('body .carousel-indicators li.active').hasClass('last') ) {
      $('body').find('.carousel-indicators li.active').removeClass('active');
      $('body').find('.carousel-indicators li.first').addClass('active');
    } else {
      $('body').find('.carousel-indicators li.active').removeClass('active').next().addClass('active');
    }
  });

  // "Start a lesson" / "Prompt lesson modal"
  $('a.prompt-lesson-start').on('click', function() {
    $('#lesson-start-modal').modal();
    var lessonTitle = $(this).data('lesson-title'); // Get destination title
    var lessonURL = $(this).data('lesson-url'); // Get destination URL
    $('#lesson-start-modal').find('.modal-body h3').html(lessonTitle); // Replace modal title with destination title
    $('#lesson-start-modal').find('.modal-footer a.start-lesson').attr( 'href', lessonURL ); // Update modal start destination URL
  });

  // "Abort a lesson"
  $('a.abort-lesson').on( 'click', function() {
    $('#lesson-start-modal').modal('hide');
  });


}

/**
 *
 *  General Lesson Scripts
 *
 */

// "Finish Lesson"
$('a.finish-lesson').on( 'click',function() {
  var lesson_id = $('.lesson-container').data('lesson-id');
  var lesson_outcome = $(this).data('lesson-outcome');
  var currency_type_id = $(this).data('currency-type-id');
  var landing_id = $(this).data('landing-id');
  // Put together outcome of lesson for scoring
  var lesson_results = [];
  // Assessment Cards
  if ( $('.lesson-card.test-card').data('lesson-object-id') ) {
    $('.lesson-card.test-card').each(function(){
      var lessonObjectID = $(this).data('lesson-object-id');
      var lessonObjectResult = $(this).data('lesson-object-result');
      var tempString = lessonObjectID+","+lessonObjectResult;
      lesson_results.push(tempString);
    });
  }
  // Instructional Cards
  if ( $('.lesson-card.learn-card').data('lesson-object-id') ) {
    $('.lesson-card.learn-card').each(function(){
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
// Increment lei counter
$(document).on( 'click', '.advance-lesson', function() {
  // Advance
  if ( ! $('.lesson-progress.current').is(':last') ) {
    $('.lesson-progress').find('.lei-counter.current').removeClass('current').next().addClass('current').addClass('viewed');
  }
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
    $('.advance-lesson').hide();
  } else {
    $('.finish-lesson').hide();
    $('.advance-lesson').show();
  }
}

function disableLessonChoices() {
  $('.lesson-card-assessment-option').attr('disabled', true);
  $('.lesson-card-assessment-option').on('click',function(e) {
    e.preventDefault();
  });
}

function playCurrentCardAudio() {
  if ( $('.lesson-card.current').find('audio').length ) {
    $('.lesson-card.current').find('audio').get(0).play();
    // Prep to allow for pause
    $('.lesson-card.current').find('.play-audio').hide();
    $('.lesson-card.current').find('.pause-audio').show();
  }
}
$('.play-audio').on('click',playCurrentCardAudio);

function pauseCurrentCardAudio() {
  if ( $('.lesson-card.current').find('audio').length ) {
    $('.lesson-card.current').find('audio').get(0).pause();
    // Prep to allow for resume
    $('.lesson-card.current').find('.play-audio').show();
    $('.lesson-card.current').find('.pause-audio').hide();
  }
}
$('.pause-audio').on('click',pauseCurrentCardAudio);

function playCurrentLearnCardAudio() {
  playCurrentCardAudio();
  if ( $('.lesson-card.current').find('audio').length ) {
    $('.lesson-card.current').find('audio').on('ended',function(){
      showLessonControls();
      $('.lesson-card.current').find('.play-audio').show();
      $('.lesson-card.current').find('.pause-audio').hide();
    });
  } else {
    showLessonControls();
  }
}

function playCurrentTestCardAudio() {
  playCurrentCardAudio();
  if ( $('.lesson-card.current').find('audio').length ) {
    $('.lesson-card.current').find('audio').on('ended',function(){
      $('.lesson-card.current').find('.play-audio').show();
      $('.lesson-card.current').find('.pause-audio').hide();
    });
  } else {
    showLessonControls();
  }
}

// # Play Feedback Pronunciation
//   Currently only used on phrases but
//   can be expanded elsewhere just make sure to avoid collisions.
if ( $('body').hasClass('single-phrases_lessons') ) {
  var playFeedbackAudio = function() {
    $('.lesson-feedback').find('audio').get(0).play();
  }
  // Click event handler
  $('.play-audio').on('click', playFeedbackAudio);
  // Play audio Feedback
  // if ( $('.pronunciation').is(':visible') ) {
  //   playFeedbackAudio();
  // }
}

var showTranslation = function() {
  $(this).parent().find('.translation').toggleClass('hidden', 'visible');
  if ( $(this).parent().find('.translation').is(':visible') ) {
    $(this).find('span').html('Hide');
  } else {
    $(this).find('span').html('Show');
  }
}
$(document).on('click', '.show-translation', showTranslation);

/*****************************************************
 ***************  Instructional Lesson  **************
 *****************************************************/
if ( $('body').hasClass('single-instruction_lessons') ) {

  function advanceInstructionalText() {
    $('.lesson-header').find('.lesson-instructions.current').removeClass('current').next().addClass('current');
  }

  // Lesson Start
  playCurrentLearnCardAudio();

  var lessonAdvanceHandler = function() {
    // Show next lesson card
    $('.lesson-content').find('.lesson-card.current').removeClass('current').next().addClass('current');
    // Advance progress bar
    updateProgressBar('correct');
    advanceProgressBar();
    advanceInstructionalText();
    // Reset feedback and controls for next assessment
    //hideFeedback();
    showLessonControls();
  }
  $('.advance-lesson').on( 'click', lessonAdvanceHandler); // Student advances lesson
}

/**
 * Video Lesson
 */
if ( $('body').hasClass('single-video_lessons') ) {

  $('.finish-lesson').show();

} // video lesson


/*****************************************************
 ***************  Listen/Repeat Lesson  **************
 *****************************************************/
if ( $('body').hasClass('single-listenrepeat_lessons') ) {

  // Lesson Start
  playCurrentLearnCardAudio();

  var lessonAdvanceHandler = function() {
    // Show next lesson card
    $('.lesson-content').find('.lesson-card.current').removeClass('current').next().addClass('current');
    // Play next audio, then show controls
    playCurrentLearnCardAudio();
    // Advance progress bar
    updateProgressBar('correct');
    advanceProgressBar();
    // Hide controls again
    $('.finish-lesson, .advance-lesson').hide();
  }
  $('.advance-lesson').on( 'click', lessonAdvanceHandler); // Student advances lesson

}

/*****************************************************
 **************  Readings Lesson Scripts *************
 *****************************************************/
if ( $('body').hasClass('single-readings') ) {

  playCurrentLearnCardAudio();

  showLessonControls();

  // Since we can't determine the what objects will appear on a reading lesson
  // let's add an 'active' class to the first child of the parent div for
  // visual lesson materials to have something visible by default
  $('.readings-texts').find(':first-child').addClass('active');

  $('.toggle-original-newspaper').on('click',function(){
    $('.readings-texts').find('div').removeClass('active');
    $('.readings-texts').find('.original-newspaper').addClass('active');
  });

  $('.toggle-typed-newspaper').on('click',function(){
    $('.readings-texts').find('div').removeClass('active');
    $('.readings-texts').find('.typed-newspaper').addClass('active');
  });

  $('.toggle-typed-newspaper-with-okinas-and-kahako').on('click',function(){
    $('.readings-texts').find('div').removeClass('active');
    $('.readings-texts').find('.typed-newspaper-with-okinas-and-kahako').addClass('active');
  });
}

/*****************************************************
 **************  Assessment Lesson Scripts ***********
 *****************************************************/
if ( $('body').hasClass('single-pronoun_lessons') || $('body').hasClass('single-phrases_lessons') || $('body').hasClass('single-vocabulary_lessons') || $('body').hasClass('single-song_lessons') || $('body').hasClass('single-chants_lessons') ) {

  function showInstructions() {
    if ( $('.lesson-card.current').hasClass('learn-card') ) {
      $('.lesson-instructions.learn-instructions').show();
      $('.lesson-instructions.test-instructions').hide();
    } else {
      $('.lesson-instructions.learn-instructions').hide();
      $('.lesson-instructions.test-instructions').show();
    }
  }

  function advanceInstructionalText() {
    $('.lesson-header').find('.lesson-instructions.current').removeClass('current').next().addClass('current');
  }


  // ###  Event handler when user selects a choice.
  var choiceHandler = function(e) {

    // #  Find the correct answer...
    if ( $('body').hasClass('single-vocabulary_lessons') ) {
      // If user get's vocab wrong, display the correct answer for them. (currently in english)
      var correctAnswer = $(this).parent().find('.correct-option img').attr('alt');
    } else if ( $('body').hasClass('single-phrases_lessons') ) {
      // Display appropriate feedback
      var correctAnswer = $(this).parent().find('.correct-option').text();
      var correctAnswerAudio = $(this).parent().find('.correct-option').data('correct-option-audio');
    } else {
      // Display appropriate feedback
      var correctAnswer = $(this).parent().find('.correct-option').text();
    }

    // ### Update scores and show feedback
    // If choice is correct...
    if ( $(this).hasClass('correct-option') ) {
      updateScore('correct');
      showFeedback('correct');
      updateProgressBar('correct');
    // If choice is incorrect...
    } else {
      updateScore('incorrect');
      showFeedback('incorrect');
      updateProgressBar('incorrect');
      updateKarma();
      // Displays correct answer in lesson feedback
      $('.lesson-feedback .lesson-feedback-incorrect .lesson-feedback-correct-option').html(correctAnswer);
      // Load correct answer audio
      $('.lesson-feedback .pronunciation-container').append('<audio class="pronunciation" src="' + correctAnswerAudio + '"></audio>');
      //$('.lesson-feedback .pronunciation').attr( 'src', correctAnswerAudio );
    }

    // Show proper lesson controls
    showLessonControls();

    // Disable choice options after click
    $('.lesson-card-assessment-option').attr('disabled', true).unbind('click');
  }
  $('.lesson-card-assessment-option').on( 'click', choiceHandler ); // Student selects choice

  /**
   *  "Next" button event handler
   */
  var lessonAdvanceHandler = function() {
    // Show next lesson card
    $('.lesson-content').find('.lesson-card.current').removeClass('current').next().addClass('current');
    // Advance progress bar
    advanceProgressBar();
    advanceInstructionalText();
    // Reset feedback and controls for next assessment
    hideFeedback();

    // Reset controls & re-enable click for choices
    $('.finish-lesson, .advance-lesson').hide();
    $('.lesson-card-assessment-option').attr('disabled', false);
    $('.lesson-card-assessment-option').bind('click', choiceHandler);
  }
  $('.advance-lesson').on( 'click', lessonAdvanceHandler); // Student advances lesson

} // End Assessment Lesson Scripts

/****************************************************
 ***************  Vocabulary Lesson Scripts *********
 ****************************************************/
if ( $('body').hasClass('single-vocabulary_lessons') ) { // Only run on "Vocabulary Lesson" pages.

  // On vocab start, show proper instructions
  showInstructions();

  // Then, play the first audio clip.
  if ( $('.lesson-card.current').hasClass('learn-card') ) {
    playCurrentLearnCardAudio();
  } else {
    playCurrentTestCardAudio();
  }


  // Modify .advance-lesson functionality
  $('.advance-lesson').unbind('click');
  var lessonAdvanceHandler = function() {

    // Prep for next card
    if ( $('.lesson-card.current').hasClass('learn-card') ) {
      updateProgressBar('correct'); // automatically mark vocabulary progress as correct
    }
    hideFeedback();
    advanceProgressBar();

    // Show next lesson card
    $('.lesson-content').find('.lesson-card.current').removeClass('current').next().addClass('current');
    // Play Appropriate audio
    if ( $('.lesson-card.current').hasClass('learn-card') ) {
      playCurrentLearnCardAudio();
      showInstructions();
    } else {
      playCurrentTestCardAudio();
      showInstructions();
    }

    // Reset controls & re-enable click for choices
    $('.finish-lesson, .advance-lesson').hide();
    $('.lesson-card-assessment-option').attr('disabled', false);
    $('.lesson-card-assessment-option').bind('click', choiceHandler);

  }
  $('.advance-lesson').on( 'click', lessonAdvanceHandler );

}

}); // end jQuery
