<?php 

   // mostra erros do php
   ini_set ( 'display_errors' , 1); 
   error_reporting (E_ALL);   
   
   include("util.php");

   // calcula hoje
   $hoje = date('Y-m-d');
   // calcula ontem
   $ontem = date('Y-m-d',(strtotime ( '-1 day' , strtotime ( $hoje ) ) ));
  
   echo "
     <form action='' method='POST'>
      Data inicial<br><input type='date' name='datai' value='$ontem'><br>
      Data final<br><input type='date' name='dataf' value='$ontem'><br>
      <input type='radio' name='op' value='html' checked> Mostrar na tela<br>
      <input type='radio' name='op' value='pdf'> Gerar PDF<br>
      <br>
      <input type='checkbox' name='preview'>Gerar e Pré-Visualizar (Atencao: marque o botao gerar pdf alem da checkbox!)<br>
      <br>
      <input type='submit' value='Gerar'>
     </form> ";

   if ( $_POST ) {
      // faz conexao 
      $conn = conecta();

      $datai = $_POST['datai'];
      $dataf = $_POST['dataf'];
      $op = isset($_POST['op']) ? $_POST['op'] : 'html';
      $preview = isset($_POST['preview']);

      $SQLCompra = 
              "SELECT tbl_compra.cod_compra, tbl_compra.data_compra, tbl_usuario.nome_usuario, tbl_compra.valor_compra
              from tbl_compra
                inner join tbl_usuario on tbl_compra.cod_usuario = tbl_usuario.cod_usuario 
                inner join tbl_compraproduto on tbl_compraproduto.cod_compra = tbl_compra.cod_compra
                inner join tbl_produto on tbl_produto.cod_produto = tbl_compraproduto.cod_produto 
              where 
                tbl_compra.data_compra >= :datai and tbl_compra.data_compra <= :dataf and
                tbl_compra.status_compra = 'Concluída'  
              group by 
                tbl_compra.cod_compra, tbl_compra.data_compra, tbl_usuario.nome_usuario
              order by tbl_compra.data_compra";

      $SQLItensCompra = 
                "SELECT 
                tbl_produto.descricao_produto, 
                tbl_compraproduto.quantidade_prod,
				tbl_produto.preco_produto,
                tbl_compra.valor_compra
            FROM 
                tbl_compra
            INNER JOIN 
                tbl_compraproduto ON tbl_compra.cod_compra = tbl_compraproduto.cod_compra
            INNER JOIN 
                tbl_produto ON tbl_compraproduto.cod_produto = tbl_produto.cod_produto
            WHERE 
                tbl_compraproduto.cod_compra = :cod_compra  
            ORDER BY 
                tbl_produto.descricao_produto"; 
  
      // formata valores em reais 
      setlocale(LC_ALL, 'pt_BR.utf-8', );

      $html = "<html>";

      // abre a consulta de COMPRA do periodo
      $compra = $conn->prepare($SQLCompra);
      $compra->execute ( [ 'datai' => $datai, 
                          'dataf' => $dataf ] );
      // prepara os ITENS     
      $itens_compra = $conn->prepare($SQLItensCompra);
      $timestamp = date('Ymd_His');
      $nomepdf = "relatorios/relatorio_$timestamp.pdf";
      /////////////  M E S T R E ////////////////////   
      // carrega a proxima linha COMPRA
      $html .= "<br><br>
        <b>" .
              sprintf('%5s', 'Id').
              sprintf('%22s','Data').
              sprintf('%44s','Nome').
              sprintf('%36s','Total').
              "</b>
        <br>";

      while ( $linha_compra = $compra->fetch() )  
      {
        $cod_compra = sprintf('%18s',$linha_compra['cod_compra']);
        $data       = sprintf('%29s',$linha_compra['data_compra']);
        $cliente    = sprintf('%30s',$linha_compra['nome_usuario']);
        $total      = sprintf('%33s',number_format($linha_compra['valor_compra'], 2, ',', '.'));
        
        $html .= $cod_compra . $data . $cliente . $total . "<br>";              
      
    $itens_compra = $conn->prepare($SQLItensCompra);

    // executa ITENS passando o código da COMPRA atual
    $itens_compra->execute(['cod_compra' => $linha_compra['cod_compra']]);

        $html .= "<b>".
              sprintf('%20s','Prod').
              sprintf('%20s','Qtd').
              sprintf('%30s','Valor Sem Promoção').
              //sprintf('%20s','Subtotal').
              "</b><br>";
        // carrega a proxima linha ITENS_COMPRA
        while ( $linha_itens_compra = $itens_compra->fetch() ) 
        { 
          $produto  = sprintf('%20s',$linha_itens_compra['descricao_produto']);
          $qtd      = sprintf('%5s',$linha_itens_compra['quantidade_prod']);
          $unit     = sprintf('%10s',number_format($linha_itens_compra['preco_produto'], 2, ',', '.'));

          $html .= $produto . $qtd . $unit . "<br>";
        } 
      }
      

      $html.="</html>";

      if($op == 'html'){

        echo $html;

      }
      else if($op == 'pdf'){
        if (CriaPDF ( 'Relatorio de Vendas', $html, $nomepdf)){
            echo 'Gerado com sucesso';
            if($preview)
            {
              header('Location: '.$nomepdf);
              exit;
            }
        }else{ 
          echo 'Erro ao gerar';
        }
}
   }

   echo "<br><a href='index.php'>Home</a>"; 
?>
