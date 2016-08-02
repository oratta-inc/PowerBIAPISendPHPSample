<?php

//const値
//PBI:PowerBI, ADD:AzureActiveDirectory
const PBI_BASE_URL = 'https://api.powerbi.com';
const PBI_URL_LIST_DATASETS = '/v1.0/myorg/datasets';
const PBI_URL_LIST_TABLES = '/v1.0/myorg/datasets/%s/tables';
const PBI_URL_ADD_ROWS = '/v1.0/myorg/datasets/%s/tables/%s/rows';

const ADD_CLIENT_ID = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';
const ADD_CLIENT_SECRET = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=';
const PBI_USER_NAME = 'hogehoge@hogehoge.com';
const PBI_PASSWORD = 'password';

const MICROSOFT_OAUTH_URL = 'https://login.microsoftonline.com/common/oauth2/token';
const PBI_RESOURCE_URL = 'https://analysis.windows.net/powerbi/api';

const SAMPLE_DATA_SOURCE_ID = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';
const SAMPLE_TABLE_ID = 'Product';

/**
 * リクエスト用メソッド
 * @param $url
 * @param $header
 * @param $data
 * @return array
 * @internal param string $contentType
 */
function doHttpRequest($url, $header, $data, $method = 'POST')
{
  $header[] = 'Content-Length:' . strlen($data);
  $context = [
      'http' => [
          'method'  => $method,
          'header'  => implode("\r\n", $header),
          'content' => $data
      ]
  ];
  $content = file_get_contents($url, false, stream_context_create($context));
  if ($content != false) {
    $content = json_decode($content);
  }
  return [
      'content'=> $content,
      'headers'=> $http_response_header,

  ];
}

/**
 * AccessTokenContentの取得
 * @return mixed
 * @internal param $url
 * @internal param $data
 */
function getAccessTokenContent()
{
  $data = http_build_query([
      'grant_type'    => 'password',
      'resource'      => PBI_RESOURCE_URL,
      'client_id'     => ADD_CLIENT_ID,
      'client_secret' => ADD_CLIENT_SECRET,
      'username'      => PBI_USER_NAME,
      'password'      => PBI_PASSWORD,
  ], '', '&');
  $header = [
      "Content-Type:application/x-www-form-urlencoded",
      "return-client-request-id:true",
  ];
  $result = doHttpRequest(MICROSOFT_OAUTH_URL, $header, $data);
  if ($result) {
    return $result['content'];
  }else{
    return null;
  }
}

function debugPrint($param){
  print '<pre>';
  print_r($param);
  print '</pre>';
}


/**
 * main
 */
function main(){
  /**
   * アクセストークン取得
   */
  var_dump('hoge');
  $tokenContent = getAccessTokenContent();
  var_dump($tokenContent);
  if(is_null($tokenContent)) die('アクセストークン取得失敗');

  /**
   * データセットの一覧を取る
   */
  $url = PBI_BASE_URL . PBI_URL_LIST_DATASETS;
  $header = [
      "Authorization:{$tokenContent->token_type} {$tokenContent->access_token}",
  ];
  $result = doHttpRequest($url, $header, '', 'GET');
  debugPrint($result);
  //$result['header'] = 'HTTP/1.1 200 OK'をチェックする

  /**
   * データセットがない場合、新たにデータセットを作る処理を入れる
   */

  /**
   * テーブル一覧を取る
   */
  $url = PBI_BASE_URL . PBI_URL_LIST_TABLES;
  $url = sprintf($url,SAMPLE_DATA_SOURCE_ID);
  $header = [
      "Authorization:{$tokenContent->token_type} {$tokenContent->access_token}",
  ];
  $result = doHttpRequest($url, $header, '', 'GET');
  debugPrint($result);

  /**
   * デーブルがないなら、テーブルスキーマを更新する処理を入れる
   */

  /**
   * データソースを追加
   */
  $url = PBI_BASE_URL . PBI_URL_ADD_ROWS;
  $url = sprintf($url, SAMPLE_DATA_SOURCE_ID, SAMPLE_TABLE_ID);
  $header[] = "content-type: application/json";
  $data = [
      "rows" => [
          [
              "Category" => "Components",
              "IsCompete" => true,
              "ManufacturedOn" => date('Y-m-d H:i:s'),
              "Name" => "Adjustable Race",
              "NewColumn" => "",
              "ProductID" => 1
          ]
      ]
  ];
  $result = doHttpRequest($url, $header, json_encode($data), 'POST');
  debugPrint($result);
}

main();