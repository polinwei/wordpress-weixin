<?php
  add_action( 'admin_ajax_notices', 'my_action_button' );

  function my_action_button() {
    $id   = 4321;
    $data = array(
        'data-nonce' => wp_create_nonce( MY_ACTION_NONCE . $id ),
        'data-id'    => $id,
    );
    echo get_submit_button( "Ajax Primary", 'primary large', 'my-action-button-1', FALSE, $data );

    $id   += 1234;
    $data = array(
        'data-nonce' => wp_create_nonce( MY_ACTION_NONCE . $id ),
        'data-id'    => $id,
    );
    echo get_submit_button( "Ajax Secundary", 'secondary', 'my-action-button-2', FALSE, $data );
  }
  my_action_button();


  add_action( 'admin_footer', 'my_action_javascript' ); // Write our JS below here

  function my_action_javascript() {
      ?>
      <script type="text/javascript">
          jQuery(document).ready(function ($) {
              $('#my-action-button-1,#my-action-button-2').click(function () {
                  var $button = $(this);
  
                  var data = {
                      'action': 'my_action',
                      'id': $button.data('id'),
                      'nonce': $button.data('nonce')
                  };
                  // Give user cue not to click again
                  $button.addClass('disabled');
                  // Invalidate the nonce
                  $button.data('nonce', 'invalid');
  
                  $.post(ajaxurl, data, function (response) {
                      alert('Got this from the server: ' + response);
  
                  });
              });
          });
      </script>
      <?php
  }