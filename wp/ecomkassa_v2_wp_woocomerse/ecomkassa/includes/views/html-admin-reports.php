<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$reports_list->prepare_items();
?>

<h2>Заявки</h2>

    <form id="nds-user-list-form" method="get">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php
		$reports_list->search_box('Поиск', 'nds-user-find');
		$reports_list->display();
		?>
    </form>

<?php 


?>