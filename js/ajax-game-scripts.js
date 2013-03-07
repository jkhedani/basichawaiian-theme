jQuery(document).ready(function($) {

  // VOCABULARY GAME: Step One: Get The Category, Display The Difficulty
  // When user clicks game category button
  $('#vocabulary-games a.vocabulary-category').click(function(){
    // Store category slug in variable
    var vocab_cat = $(this).attr('data-category');

    // post data to function
    $.post(ajax_scripts.ajaxurl, {
        dataType: "jsonp",
        action: 'get_game_category',
        nonce: ajax_scripts.nonce,
        gameCategory: vocab_cat
      }, function(response) {
        if (response.success===true) {
          $('#vocabulary-games').children().fadeOut('slow',function(){
            //$('#vocabulary-categories').children().hide(1,function(){
              $('h1.entry-title').css('display','none');
              $('#vocabulary-games').append().html(response.html);
            //});
          });
        } else {
          // Bad Response message
        }
    });
  }); // end click

  // VOCABULARY GAME: Step Two: Get The Difficulty, Display The Game
  // When user clicks game category button
  // NOTE: .on() is used to fire after ajax returns (similar function to .live())
  $(document).on('click', '#vocabulary-games a.difficulty-level', function() {
  //$('#vocabulary-games a.difficulty-level').click(function(){
    // Store category slug in variable

    var vocab_difficulty = $(this).attr('data-difficulty');
    var vocab_cat = $(this).attr('data-category');

    // post data to function
    $.post(ajax_scripts.ajaxurl, {
        dataType: "jsonp",
        action: 'get_game_difficulty',
        nonce: ajax_scripts.nonce,
        gameDifficulty: vocab_difficulty,
        gameCategory: vocab_cat
      }, function(response) {
        if (response.success===true) {
          $('#vocabulary-games').children().fadeOut('slow',function(){
            $('#vocabulary-games').append().html(response.html);
            // Play the first game card after one second...
            //setTimeout(function() {
              $('#vocabulary-games .gameCard.current').find('audio.pronunciation').get(0).play();
            //}, 1000);
          });
        } else {
          alert('!');
          // Bad Response message
        }
    });
  }); // end click

}); // End jQuery