<?php
session_start();
require_once '../vendor/autoload.php';
use Elasticsearch\ClientBuilder;
$client = ClientBuilder::create()->setHosts(['127.0.0.1:9200'])->build();

$output = '';
if(isset($_POST["query"]))
{
 $link='';
 // $description='';
 $search = $_POST["query"];
 $params=[
    /*
    'body' => [      
      'query' => [
        'bool' => [
          'must' => [
            'multi_match' => [
              'fields' => ['title','University','Professor'],
              'query' => $search
            ]
          ]
        ]
      ],
      'from' => 0,
      'size' => 350
    ]
  ];
  */
  'body'=>[
  'query' => [
    'bool'=> [
      'should'=> [
        [
          'multi_match'=> [
            'query'=> $search,
            'fields'=> ['title','Cateagory','Cateagory.english','University','University.english','Level :','Course Type :'],
            'type'=> 'most_fields',
            'boost'=> 3,
            'minimum_should_match'=> '75%',
            'cutoff_frequency'=> 0.10,
            'fuzziness'=>'AUTO'
          ]
        ],
        [
          'multi_match'=> [
            'query'=> $search,
            'fields'=> ['Duration','Level :','Professor']
            , 'type'=> 'most_fields',
            'minimum_should_match'=> '75%', 
            'cutoff_frequency'=> 0.10
          ]
        ]
      ]
    ]
  
],
  'from'=>0,
  'size'=>350
  // 'highlight'=> [
  //   'type'=>'unified',
  //   'fields'=> [
  //     '*'=>[
        
  //     ]
  //   ]
  // ]
]
];

 $query = $client->search($params);

 $n = count($query['hits']['hits']);
 // $n = 'track_total_hits';

 // $query = $client->search($params2);
 $query = $client->search($params);

  if($query['hits']['total']>=1){
    $results = $query['hits']['hits'];
  }

}

if(isset($results))
{
 $output .= '
  <div style="font-size: 18px; margin-bottom: 15px; margin-top: -10px;">Search results for "<b>'.$search.'</b>"</div>
  <div style="margin-bottom: 5px; margin-top: -10px;">Found '.$n.' results</div>
 ';
 foreach($results as $r)
 {

  if(isset($r["_source"]["link"])){
  $link = $r["_source"]["link"];
  $output .='
    <div class="card card-1">
    <div id="sticky_header" class="title_header">
      <div class="title"><a class="title_link" href="' . $link . '">'.$r["_source"]["title"].'</a></div>
      <div class="link"><a href="' . $link . '">'.$r["_source"]["link"].'</a></div>
      <hr style="margin-top: 10px;">
    </div>
    <div class="description" style="padding-bottom: 10px;">
    <div style="padding-bottom: 5px;"><b>Description:</b></div>
    <div>'.$r["_source"]["description"].'</div>
    </div>
    <hr style="margin-top: -5px;">
    <div class="div_university">
      <div class="professor" style="font-size: 15px; margin-top: -10px;  margin-bottom: 5px;"><b>Guided '.$r["_source"]["Professor"].'</b></div>
      <div class="university" style="font-size: 15px; margin-top: -5px; margin-bottom: 10px;"><b>Offered By: </b><em style="font-size: 15px;">'.$r["_source"]["University"].'</em></div>
    </div>
    </div>
  ';
  }
  
 }

 echo $output;
}
else
{
 echo 'No data found';
}
?>