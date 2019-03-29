<?php

class Ifthenpay_Ifmb_CallbackController extends Mage_Core_Controller_Front_Action
{
	public function checkAction ()
	{
	$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		//$read = Mage::getSingleton( 'core/resource' )->getConnection( 'core_read' ); // To read from the database
		 
		
		$query = "SELECT * FROM ifthenpay_ifmb_config ORDER BY id ASC LIMIT 1";
		

		$stmt = $db->prepare($query);
		$stmt->execute();
	
    
		if ($result = $stmt->fetch()) {
			if( $this->getRequest()->getParam('key') == $result["antiphishing"]){
			
				//$order = Mage::getModel('sales/order')->load($incrementId, 'increment_id');
				//$id = $order->getId();
				$entidade = $this->getRequest()->getParam('entidade');
				$referencia = $this->getRequest()->getParam('referencia');
				$valor = number_format($this->getRequest()->getParam('valor'),2);
				
				
				
				
				$db = Mage::getSingleton( 'core/resource' )->getConnection( 'core_read' ); // To read from the database
				
				$query = "SELECT * FROM ifthenpay_ifmb_callback WHERE entidade=:entidade and referencia_sem_espacos=:referencia and valor=:valor and check_mb is null ORDER BY id DESC LIMIT 1";
				
				$binds = array(
					'entidade' => $entidade,
					'referencia' => $referencia,
					'valor' => $valor
				);
				
				$result = $db->query( $query, $binds );
				
				if($row = $result->fetch()){
					$oid = $row["order_id"];
					
					$order = Mage::getModel('sales/order')->load($oid, 'increment_id');
					$id = $order->getId();
					
					try {
						$invoice = $order->prepareInvoice();
 
						$invoice->register();
						Mage::getModel('core/resource_transaction')
						   ->addObject($invoice)
						   ->addObject($invoice->getOrder())
						   ->save();
				 
						$invoice->sendEmail(true, '');
						$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);        
						$order->save();
						

						 $sql = "UPDATE ifthenpay_ifmb_callback SET `check_mb` = 'done' WHERE `order_id` = ".$oid;
						 $db->query($sql);
						 
						 echo "Check Callback Done";
					}
					catch (Exception $e) {
						print_r($e);
						echo 'error';
					} 
				}
				else
				{
					//Pagamento não encontrado
					echo "ERRO P404";
				}
			}
			else
			{
				//Chave Anti-Phishing Inválida
				echo "ERRO K203";
			}
		}else{
			//Chave Anti-Phishing não configurada. Tem de Activar primeiro o Callback.
			echo "ERRO K404";
		}
	}
}

?>