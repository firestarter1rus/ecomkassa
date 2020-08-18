<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<h2>Настройки кассы E-COM kassa</h2>
<div>
    <form method="post" name="settings_form">
        <table class="form-table">
            <tr>
                <th>
                    <label>API Url:</label>
                </th>
                <td>
                    <input type="text" name="ecomkassa_server_url"
                           value="<?php echo get_option( "ecomkassa_server_url" ) ?>"/>
                </td>
            </tr>
            <tr>
                <th>
                    <label>Идентификатор магазина:</label>
                </th>
                <td>
                    <input type="text" name="ecomkassa_shop_id"
                           value="<?php echo get_option( "ecomkassa_shop_id" ) ?>"/>
                </td>
            </tr>
            <tr>
                <th>
                    <label>Логин:</label>
                </th>
                <td>
                    <input type="text" name="ecomkassa_login" value="<?php echo get_option( "ecomkassa_login" ) ?>"/>
                </td>
            </tr>
            <tr>
                <th>
                    <label>Пароль:</label>
                </th>
                <td>
                    <input type="password" name="ecomkassa_password"
                           value="<?php echo get_option( "ecomkassa_password" ) ?>"/>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="tax_system">Система налогообложения:</label>
                </th>
                <td>
                    <select name="ecomkassa_tax_system" id="tax_system">
						<?php foreach ( Ecom_Kassa()->taxSystems() as $val => $name ): ?>
                            <option value="<?php echo $val ?>" <?php echo get_option( "ecomkassa_tax_system" ) == $val ? "selected" : "" ?>><?php echo $name ?></option>
						<?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="tax_system">Статус заказа при котором будет фискализирован чек прихода:</label>
                </th>
                <td>
                    <select id="order_status" name="ecomkassa_fiscalize_on_order_status_sell">
						<?php
						$statuses = wc_get_order_statuses();
						foreach ( $statuses as $status => $status_name ):
							$status = str_replace( 'wc-', '', $status );
							?>
                            <option value="<?php echo esc_attr( $status ) ?>" <?php echo selected( $status, get_option( "ecomkassa_fiscalize_on_order_status_sell" ), false ) ?>><?php echo esc_html( $status_name ) ?></option>
						<?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="tax_system">Статус заказа при котором будет фискализирован чек возврата прихода:</label>
                </th>
                <td>
                    <select id="order_status" name="ecomkassa_fiscalize_on_order_status_sell_refund">
						<?php
						foreach ( $statuses as $status => $status_name ):
							$status = str_replace( 'wc-', '', $status );
							?>
                            <option value="<?php echo esc_attr( $status ) ?>" <?php echo selected( $status, get_option( "ecomkassa_fiscalize_on_order_status_sell_refund" ), false ) ?>><?php echo esc_html( $status_name ) ?></option>
						<?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="tax_system">Методы оплаты заказа при которых будет фискализирован чек:</label>
                </th>
                <td>
					<?php
					$wc_gateways = new WC_Payment_Gateways();
					$gateways    = $wc_gateways->get_available_payment_gateways();

					$selected_options = get_option( "ecomkassa_fiscalize_on_available_payment_gateways" );

					foreach ( $gateways as $gateway ) {

						if ( $gateway->enabled == 'yes' ) {
							if ( ! $selected_options ) {
								$checked = '';
							} else {
								$checked = in_array( $gateway->id, $selected_options ) ? ' checked="checked" ' : '';
							}

							?>
                            <p>
                                <input type="checkbox" id="payment_gateways-<?= $gateway->id ?>"
                                       name="ecomkassa_fiscalize_on_available_payment_gateways[]"
                                       value="<?php echo esc_attr( $gateway->id ) ?>" <?= $checked ?> />
                                <label for="payment_gateways-<?= $gateway->id ?>"><?php echo esc_html( $gateway->title ) ?></label>
                            </p>
							<?php
						}
					}
					?>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input class="button button-primary" type="submit" value="Cохранить" name="submit">
        </p>
    </form>
</div>
