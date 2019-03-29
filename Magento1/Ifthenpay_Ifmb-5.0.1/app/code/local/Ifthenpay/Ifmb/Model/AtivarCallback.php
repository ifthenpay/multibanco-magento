<?php

class Ifthenpay_Ifmb_Model_AtivarCallback extends Mage_Core_Model_Config_Data
{
    public function getCommentText(Mage_Core_Model_Config_Element $element, $currentValue)
    {
		$chaveap=md5(time());
		
		$read = Mage::getSingleton( 'core/resource' )->getConnection( 'core_read' ); // To read from the database
		 
		
		$query = "SELECT * FROM ifthenpay_ifmb_config ORDER BY id ASC LIMIT 1";
		

		$stmt = $read->prepare($query);
		$stmt->execute();
	
    
		if ($result = $stmt->fetch()) {
			$chaveap = $result["antiphishing"];
		}else{
			$ifmb_save_conn = Mage::getSingleton('core/resource')->getConnection('core_write');
			$ifmb_save_conn->beginTransaction();
			$fields = array();
			$fields['antiphishing'] = $chaveap;
			$ifmb_save_conn->insert('ifthenpay_ifmb_config',$fields);
			$ifmb_save_conn->commit();
		}
		
        $skinUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
        $result = "<p id='ifmb_callback_info'></p>";
        $result .= "<script type='text/javascript'>
            function render_callback_info()
            {
                var infomb = $('ifmb_callback_info');
				 var field_value = $('payment_ifmb_active_callback').getValue();
                    switch (field_value.toLowerCase())
                    {
                        case '1':
                            
                            infomb.innerHTML ='<h2>Instru&ccedil;&otilde;es Callback</h2>Para ativar o callback ter&aacute; de comunicar &agrave; Ifthenpay os seguintes elementos:<br/>- Entidade e Subentidade<br/>- 4 &uacute;ltimos d&iacute;gitos da chave de backoffice (Fornecida no momento da ades&atilde;o. A mesma &eacute; constitu&iacute;da por 16 d&iacute;gitos num&eacute;ricos agrupados 4 a 4 apresentando o seguinte formato: 0000-0000-0000-0000)<br/>- Url de Callback: " . Mage::getBaseUrl() . "multibanco/callback/check/key/[CHAVE_ANTI_PHISHING]/entidade/[ENTIDADE]/referencia/[REFERENCIA]/valor/[VALOR]<br/>- Chave Anti-Phishing: " . $chaveap . "<br/><br/>Estes elementos dever&atilde;o ser comunicados via email (<a href=\"mailto:ifthenpay@ifthenpay.com?subject=Ativar Callback\">ifthenpay@ifthenpay.com</a>) com o assunto <strong>Ativar Callback</strong>.';
                            break;
                        case '0':
                            
                            infomb.innerHTML = '';
                            break;
                    }
            }
 
            function init_comment()
            {
                render_callback_info();
                $('payment_ifmb_active_callback').observe('change', function(){
                    render_callback_info();
                });
            }
            document.observe('dom:loaded', function(){init_comment();});
            </script>";
 
        return $result;
    }
}
?>