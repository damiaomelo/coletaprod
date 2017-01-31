<?php

    include('inc/config.php');             
    include('inc/functions.php');

    if (isset($_FILES['file'])) {    
        $fh = fopen($_FILES['file']['tmp_name'], 'r+');
        $row = fgetcsv($fh, 8192,"\t");
        while( ($row = fgetcsv($fh, 8192,"\t")) !== FALSE ) {    
            
            //print_r($row);
            //echo '<br/><br/>'; 
            
            // Monta array
            $doc_obra_array["doc"]["source"] = "Base Web of Science";
            $doc_obra_array["doc"]["source_id"] = $row[60];
            $doc_obra_array["doc"]["tag"][] = $_POST["tag"];
            if ($row[0] == "J") {
                $doc_obra_array["doc"]["tipo"] = "Artigo publicado";
            }
            $doc_obra_array["doc"]["titulo"] = str_replace('"','',$row[8]);
            $doc_obra_array["doc"]["ano"] = $row[44];
            $doc_obra_array["doc"]["idioma"] = $row[12];
            if (isset($row[54])){
                $doc_obra_array["doc"]["doi"] = $row[54];
            }
            if (isset($row[31])){
                $doc_obra_array["doc"]["citacoes_recebidas"] = $row[31];
            }      
            if (isset($row[21])){
                $doc_obra_array["doc"]["resumo"] = str_replace('"','',$row[21]);
            } 
            
            
            // Palavras chave
            $palavras_chave_authors = explode(";",$row[19]);
            $palavras_chave_wos = explode(";",$row[20]);
            $doc_obra_array["doc"]["palavras_chave"] = $palavras_chave_authors;
            $doc_obra_array["doc"]["palavras_chave"] = array_merge($palavras_chave_authors,$palavras_chave_wos);
            
            // Autores
            $autores_nome_array = explode(";",$row[5]);
            $autores_citacao_array = explode(";",$row[1]);
            $autores_json_str = [];
            
            for($i=0;$i<count($autores_nome_array);$i++){
                $doc_obra_array["doc"]["autores"][$i]["nomeCompletoDoAutor"] = $autores_nome_array[$i];
                $doc_obra_array["doc"]["autores"][$i]["nomeParaCitacao"] = $autores_citacao_array[$i];               
            }
            
            // Agência de fomento
            $agencia_de_fomento_array = explode(";",$row[27]);
            $doc_obra_array["doc"]["agencia_de_fomento"] = $agencia_de_fomento_array;

            if ($row[0] == "J") {
                $doc_obra_array["doc"]["artigoPublicado"]["tituloDoPeriodicoOuRevista"] = str_replace('"','',$row[9]);
                $doc_obra_array["doc"]["artigoPublicado"]["nomeDaEditora"] = $row[35];
                $doc_obra_array["doc"]["artigoPublicado"]["issn"] = $row[38];                                                                                      
                $doc_obra_array["doc"]["artigoPublicado"]["volume"] = $row[45];
                $doc_obra_array["doc"]["artigoPublicado"]["fasciculo"] = $row[46];                                                                                 $doc_obra_array["doc"]["artigoPublicado"]["serie"] = $row[47];
                $doc_obra_array["doc"]["artigoPublicado"]["paginaInicial"] = $row[51];                                                                             $doc_obra_array["doc"]["artigoPublicado"]["paginaFinal"] = $row[52];     
                $doc_obra_array["doc"]["artigoPublicado"]["localDePublicacao"] = $row[36];                                                                             
            }
            
            if (!empty($row[54])) {
                $sha256 = hash('sha256', ''.$row[54].'');
            } else {
                $sha256 = hash('sha256', ''.$row[60].'');
            }           

            $doc_obra_array["doc_as_upsert"] = true;
            
            $body = json_encode($doc_obra_array, JSON_UNESCAPED_UNICODE); 
            
            //print_r($body);

            $resultado_wos = elasticsearch::store_record($sha256,"trabalhos",$body);
            print_r($resultado_wos);            
            
            //Limpar variáveis
            unset($palavras_chave_authors);
            unset($palavras_chave_wos);
            unset($palavras_chave_array);
            unset($autores_array);
            unset($autores_json_str);
            unset($doc_obra_array["doc"]["tag"]);
            
        }
    }
    
    //sleep(5); 
    //echo '<script>window.location = \'http://bdpife2.sibi.usp.br/coletaprod/result_trabalhos.php?search[]=tag.keyword:"'.$_POST["tag"].'"\'</script>';

?>

    
    