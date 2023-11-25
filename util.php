<?php   

  // Geracao de pdf
  // Marcelo C Peres 2023
  /* Exemplo: 
     if ( CriaPDF ( 'Relatorio de Vendas', 
         '<html> ... </html>', 
         'relatorios/20231031.pdf' ) )  
     {
      echo 'gerado com sucesso';
     }
  */   

  function CriaPDF ( $paramTitulo, $paramHtml, $paramArquivoPDF )
  {
   $arq = false;     
   try {  
    require "html_table.php"; 
    // abre classe fpdf estendida com recurso que converte <table> em pdf
  
    $pdf = new PDF();  
    // cria um novo objeto $pdf da classe 'pdf' que estende 'fpdf' em 'html_table.php'
    $pdf->AddPage();  // cria uma pagina vazia
    $pdf->SetFont('helvetica','B',20);       
    $pdf->Write(5,$paramTitulo);    
    $pdf->SetFont('helvetica','',8);     
    $pdf->WriteHTML($paramHtml); // renderiza $html na pagina vazia
    ob_end_clean();    
    // fpdf requer tela vazia, essa instrucao 
    // libera a tela antes do output
    
    // gerando um arquivo 
    $pdf->Output($paramArquivoPDF,'F');
    // gerando um download 
    $pdf->Output('D',$paramArquivoPDF);  // disponibiliza o pdf gerado pra download
    $arq = true;
   } catch (Exception $e) {
     echo $e->getMessage(); // erros da aplicação - gerais
   }
   return $arq;
  }


  // Envio de emails
  // Marcelo C Peres 2023
  /* Exemplo: 
     if ( EnviaEmail ('fulano@fulano','Feliz Aniversario',
                      '<html><body>Feliz niver</body></html>') 
     {
      echo 'enviado com sucesso';
     }
  */   
     
  ////////////////////////////////////////////////////////////////
  function EnviaEmail ( $paramEmailDestino, $paramAssunto, $paramHtml, &$paramErro,  
                        $paramUsuario = "marcelocabello@projetoscti.com.br", 
                        $paramSenha = "MarceloC@belo", 
                        $paramSMTP = "smtp.projetoscti.com.br" )   
  {
    
   // troque usuario e senha !!!! 
   error_reporting(E_ALL);
   ini_set("display_errors", 1);
  
   require "PHPMailer-master/src/PHPMailer.php";
      
   try {
     $enviado = false;

     //cria instancia de phpmailer
     echo "<br>Tentando enviar para $paramEmailDestino...";
     $mail = new PHPMailer(); 
     $mail->IsSMTP();  
  
     // servidor smtp
     $mail->Host = $paramSMTP;
     $mail->SMTPAuth = true;      // requer autenticacao com o servidor                         
     $mail->SMTPSecure = 'tls';                            
      
     $mail-> SMTPOptions = array (
       'ssl' => array (
       'verificar_peer' => false,
       'verify_peer_name' => false,
       'allow_self_signed' => true ) );
      
     $mail->Port = 587;      
      
     $mail->Username = $paramUsuario; 
     $mail->Password = $paramSenha; 
     $mail->From = $paramUsuario; 
     $mail->FromName = "Suporte de senhas"; 
  
     $mail->AddAddress($paramEmailDestino, "Usuario"); 
     $mail->IsHTML(true); 
     $mail->Subject = $paramAssunto; 
     $mail->Body = $paramHtml;
     $enviado = $mail->Send(); 
       
     if (!$enviado) {
        echo "<br>Erro: " . $mail->ErrorInfo;
     } else {
        echo "<br><b>Enviado!</b>";
     }
      
   } catch (phpmailerException $e) {
     echo $e->errorMessage(); // erros do phpmailer
     $paramErro = $e->errorMessage();
   } catch (Exception $e) {
     echo $e->getMessage(); // erros da aplicação - gerais
     $paramErro = $e->getMessage();    
   }      

   return $enviado;         
  }


  /*
  * Função para ExecutaSQL frases sql
  * marcelo c peres - 2023
  */

  function ExecutaSQL( $paramConn, $paramSQL ) 
  {
    // exec eh usado para update, delete, insert
    // eh um metodo da conexao
    // retorna o nro de linhas afetadas
    $linhas = $paramConn->exec($paramSQL);
  
    if ($linhas > 0) { 
        return TRUE; 
    } else { 
        return FALSE; 
    }  
  }

  /*
  * Função para executasql frases sql
  * marcelo c peres - 2023
  */

  // ValorSQL 
  // retorna o valor de um campo de um select
  // Set 2023 - Marcelo C Peres 
  function ValorSQL( $pConn, $pSQL ) 
  {

   $linhas = $pConn->query($pSQL)->fetch();

   if ($linhas > 0) { 
       return $linhas[0]; 
   } else { 
       return "0"; 
   }  
  }


  /**
  * Função para gerar senhas aleatórias
  *
  * @author    Thiago Belem <contato@thiagobelem.net>
  *
  * @param integer $tamanho Tamanho da senha a ser gerada
  * @param boolean $maiusculas Se terá letras maiúsculas
  * @param boolean $numeros Se terá números
  * @param boolean $simbolos Se terá símbolos
  *
  * @return string A senha gerada
  */

  function GeraSenha($tamanho = 8, $maiusculas = true, $numeros = true, $simbolos = false)
  {
    //$lmin = 'abcdefghijklmnopqrstuvwxyz';
    $lmai = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $num = '1234567890';
    $simb = '!@#$%*-';
    $retorno = '';
    $caracteres = '';

    //$caracteres .= $lmin;
    if ($maiusculas) $caracteres .= $lmai;
    if ($numeros)    $caracteres .= $num;
    if ($simbolos)   $caracteres .= $simb;

    $len = strlen($caracteres);
    
    for ($n = 1; $n <= $tamanho; $n++) {
        $rand = mt_rand(1, $len);
        $retorno .= $caracteres[$rand-1];
    }
    
    return $retorno;
  }

  //////  funcao de conexao
  //////  14-8-2023
  function conecta ($params = "")  // igual a nada pra indicar q aceita vazio !! 
  {
    if ($params == "") {
        $params="pgsql:host=pgsql.projetoscti.com.br; dbname=projetoscti22; 
                 user=projetoscti22; password=721175";
    }

    $varConn = new PDO($params);

    if (!$varConn) {
        echo "Nao foi possivel conectar";
    } else { return $varConn; }
  }
  /////////////////////////

  //////  funcao de login
  //////  11-9-2023
  function funcaoLogin ($paramLogin, $paramSenha, &$paramAdmin)  
  {
   $conn = conecta();  
   $varSQL = " select senha,admin from usuarios 
               where usuario = '$paramLogin' "; 
   $linha =  $conn->query($varSQL)->fetch();
   $paramAdmin = $linha['admin'] == 's';
   return $linha['senha'] == $paramSenha;  
  }

  //////  funcao de definir cookie
  //////  11-9-2023
  function DefineCookie($paramNome, $paramValor, $paramMinutos) 
  {
   echo "Cookie: $paramNome Valor: $paramValor";  
   setcookie($paramNome, $paramValor, time() + $paramMinutos * 60); 
  }
?>