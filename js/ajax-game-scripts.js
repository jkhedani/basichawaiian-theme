jQuery(document).ready(function($) {

  // VOCABULARY GAME: Step One: Get The Difficulty, Display The Game
  // When user clicks game category button
  // NOTE: .on() is used to fire after ajax returns (similar function to .live())
  $(document).on('click', '#vocabulary-games a.vocabulary-category', function() {
    var vocab_cat = $(this).attr('data-category');
    var connected_to_id = $(this).attr('data-connected-to-id');

    // post data to function
    $.post(ajax_scripts.ajaxurl, {
        dataType: "jsonp",
        action: 'get_game_difficulty',
        nonce: ajax_scripts.nonce,
        connectedTo: connected_to_id,
      }, function(response) {
        if (response.success===true) {
          $('#vocabulary-games').children().fadeOut('slow',function(){
            $('#vocabulary-games').append().html(response.html);
            
            // Query the amount of games a user will play
            var totalAmountGames = $('.gameProgress .miniGame').length;
            $('.gameResults').attr('data-total-tested',totalAmountGames);
            
            // Set first and last card for progress bar and game cards
            $('.gameProgressPoint:first-child').addClass('current');
            $('.gameCard:first-child').addClass('current');
            $('.gameProgressPoint:last-child').addClass('last');
            $('.gameCard:last-child').addClass('last');

            // Play the first game card after one second...
            //setTimeout(function() {
              $('#vocabulary-games .gameCard.current').find('audio.pronunciation').get(0).play();
            //}, 1000);
          });
        } else {
          // Bad Response message
        }
    });
  }); // end click

  // VOCABULARY GAME: Step Two: Finish Game and Publish Results
  // When user clicks "Finish button"
  // NOTE: .on() is used to fire after ajax returns (similar function to .live())
  $(document).on('click', '.gameBox .gameUserControls a.gameFinish', function() {

    var total_tested = $('.gameResults').attr('data-total-tested');
    var total_correct = $('.gameResults').attr('data-total-correct');
    var objects_tested = [];
    var objects_viewed = $('.gameResults .cardsViewed').attr('data-viewed');

    $('.cardTested').each(function(){
      var object = {}; // new 'object' for each iteration...kewl
      object['id'] = $(this).attr('data-card-id');
      object['times_correct'] = $(this).attr('data-correct');
      object['times_wrong'] = $(this).attr('data-wrong');
      object['times_viewed'] = 0;
      objects_tested.push(object);
    });   

    alert(objects_viewed);
    alert(objects_tested);

    // post data to function
    $.post(ajax_scripts.ajaxurl, {
        dataType: "jsonp",
        action: 'publish_results',
        nonce: ajax_scripts.nonce,
        totalTested: total_tested,
        totalCorrect: total_correct,
        objectsViewed: objects_viewed,
        objectsTested: objects_tested
      }, function(response) {
        if (response.success===true) {
          alert('good!');
          $('#vocabulary-games').children().fadeOut('slow',function(){
            $('#vocabulary-games').append().html(response.html);
          });
        } else {
          // Bad Response message
        }
    });
  }); // end click

}); // End jQuery