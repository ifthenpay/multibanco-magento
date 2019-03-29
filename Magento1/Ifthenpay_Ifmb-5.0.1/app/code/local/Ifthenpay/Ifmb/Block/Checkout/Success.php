<?php

class Ifthenpay_Ifmb_Block_Checkout_Success extends Mage_Checkout_Block_Onepage_Success
{
	protected function _toHtml()
    {
	
		$read = Mage::getSingleton( 'core/resource' )->getConnection( 'core_read' ); // To read from the database
		 

		
		$query = "SELECT * FROM ifthenpay_ifmb_callback WHERE order_id = :order_id";
		
		$binds = array(
			'order_id' => $this->getOrderId()
		);
		$result = $read->query( $query, $binds );
		while ( $row = $result->fetch() ) {
			$ifmbInfo = '<div>
				<br/><br/><h2>' . $this->__('Utilize os dados abaixo para proceder ao pagamento da sua encomenda. Estes dados foram igualmente enviados para o seu email.') . '</h2><br/>
				<table border="0" cellspacing="1" cellpadding="0" style="margin: 0 auto;">
		<tr>
			<td style="padding-bottom: 10px;">
				<img src="' . $this->getSkinUrl('images/ifmb/multibanco_horizontal.gif'). '" border="0" alt="multibanco" title="multibanco" style="width: 100%;"/>
			</td>
		</tr>
		<tr>
			<td>
				<table width="100%" border="0" cellspacing="1" cellpadding="0">
					<tr>
						<td style="text-align: left;">' . $this->__('<strong>Entidade:</strong>') . '</td>
						<td style="text-align: right;">' . $row["entidade"] . '</td>
					</tr>
					<tr>
						<td style="text-align: left;">' . $this->__('<strong>Referência:</strong>') . '</td>
						<td style="text-align: right;padding-left: 10px;">' . $row["referencia"] . '</td>
					</tr>
					<tr>
						<td style="text-align: left;">' . $this->__('<strong>Montante:</strong>') . '</td>
						<td style="text-align: right;">' . $row["valor"] . '  EUR</td>
					</tr>
				</table>
			</td>
		</tr>
	</table></div>';
		}
		
		
        $html = parent::_toHtml();
		
		$html .= $ifmbInfo;
	

        
        return $html;
    }
}

?>