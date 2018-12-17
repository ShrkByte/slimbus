<?php

namespace Statbus\Controllers;

use Psr\Container\ContainerInterface;
use Statbus\Controllers\Controller as Controller;
// use Statbus\Controllers\User as User;


class NameVoteController Extends Controller {
  
  public function __construct(ContainerInterface $container) {
    parent::__construct($container);
    $settings = $this->container->get('settings');
    $this->alt_db = (new DBController($settings['database']['alt']))->db;
    $this->user = $this->container->get('user')->user;
  }

  public function index($request, $response, $args){
    return $this->view->render($response, 'misc/namevote.tpl',[
      'name'      => $this->getname()
    ]);
  }

  public function cast($request, $response, $args){
    $args = $request->getParams();
    if(!isset($args)){
      return json_encode(['error'=>'Missing vote arguments']);
    }

    if(!$this->user){
      return json_encode(['error'=>'You must be logged in to vote for names!']); 
    }
    try{
      $this->alt_db->insert('name_vote',[
        'name' => $args['name'],
        'good' => (bool) $args['vote'],
        'ckey' => $this->user->ckey,
      ]);
    } catch (Exception $e){
      return json_encode(['name'=>$this->getName(),'args'=>$args]); 
    }
    return json_encode(['name'=>$this->getName(),'args'=>$args]);
  }

  public function getName(){
    $name = $this->DB->row("SELECT DISTINCT `name`, job
    FROM tbl_death
    WHERE YEAR(`tod`) = 2018 AND `job` IN ('Assistant', 'Atmospheric Technician', 'Bartender', 'Botanist', 'Captain', 'Cargo Technician', 'Chaplain', 'Chemist', 'Chief Engineer', 'Chief Medical Officer', 'Cook', 'Curator', 'Detective', 'Geneticist', 'Head of Personnel', 'Head of Security', 'Janitor', 'Lawyer', 'Librarian', 'Medical Doctor', 'Quartermaster', 'Research Director', 'Roboticist', 'Scientist', 'Security Officer', 'Shaft Miner', 'Station Engineer', 'Virologist', 'Warden')
    ORDER BY RAND()
    LIMIT 0,1;");
    return $name;
  }

}