<?php
class Suppliers_x_items extends CI_Controller{
   function __construct() {
                parent::__construct();
                $this->load->library('posnic');               
    }
    function index(){     
              $this->get_items();
             //$this->load->view('annan1');
    }
    function get_items(){
                $config["base_url"] = base_url()."index.php/suppliers_x_items/get_items";
	        $config["total_rows"] =$this->posnic->posnic_module_count('suppliers'); 
	        $config["per_page"] = 8;
	        $config["uri_segment"] = 3;
	        $this->pagination->initialize($config);	 
	        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;               
                $data['count']=$this->posnic->posnic_module_count('suppliers');                 
	        $data["row"] = $this->posnic->posnic_module_limit_result('suppliers',$config["per_page"], $page);           
	        
                $data["links"] = $this->pagination->create_links();
                $this->load->view('supplier_list',$data);
    }
    function add_items($guid){
         $data['supplier_id']=$guid;
         $where=array('supplier_id'=>$guid);
         $where_sup=array('guid'=>$guid);
         $data['sup']=$this->posnic->posnic_module_where('suppliers',$where_sup);         
         $data['row']=$this->posnic->posnic_module_where('suppliers_x_items',$where);
         $data['items']=$this->posnic->posnic_module('items');
         //$data['item_row']=$this->posnic->posnic_module('items');
         $this->load->view('add_items',$data);
         
    }
    
  
    function save_items(){
        if($this->input->post('save')){       
        if($_SESSION['Posnic_Add']==="Add"){
              $sguid=  $this->input->post('s_guid');
             if($_SESSION['Posnic_Delete']==="Delete"){
                $where=array('supplier_id'=>$sguid);
                $data=$this->posnic->posnic_array_module_where('suppliers_x_items',$where);
            
            if(count($data)>0)     {
                 foreach ($data as $i_value){
                    
                     if(!$this->input->post($i_value['item_id'])){
                        $where1=array('supplier_id'=>$sguid,'item_id'=>$i_value['item_id']);
                        $this->posnic->posnic_where_delete($where1);
                       
                     }
                 }
                    }
            }
            
            $this->form_validation->set_rules('items[]', 'items', 'required');
            $this->form_validation->set_rules('cost[]', 'items', 'required');
            $this->form_validation->set_rules('sell[]', 'items', 'required');
            $this->form_validation->set_rules('quty[]', 'items', 'required');
            $this->form_validation->set_rules('mrp[]', 'items', 'required');
          
              
            $data= $this->input->post('items');
             
            $cost=  $this->input->post('cost');
            $sell=  $this->input->post('sell');
            $quty=  $this->input->post('quty');
            $discount=$this->input->post('mrp');
            $item_active=  $this->input->post('item_active');
                
            if ( $this->form_validation->run() !== false ) {                
            for($i=0;$i<count($data);$i++){
            $value=array('supplier_id'=>$sguid,'item_id'=>$data[$i]);
                        if($this->posnic->check_unique($value)){
                             $data2=array('item_id'=>$data[$i],
                                         'supplier_id'=>$sguid,
                                         'cost'=>$cost[$i],
                                         'quty'=>$quty[$i],
                                         'price'=>$sell[$i],
                                         'discount'=>$discount[$i],
                                         'item_active'=>$item_active[$i]
                                 );                             
                            $this->posnic->posnic_add($data2);
                         }else{
                              $values=array('item_id'=>$data[$i],
                                        'supplier_id'=>$sguid,
                                        'cost'=>$cost[$i],
                                         'quty'=>$quty[$i],
                                         'price'=>$sell[$i],
                                         'discount'=>$discount[$i],
                                         'item_active'=>$item_active[$i]
                                 );                          
                             $where=array('supplier_id'=>$sguid,'item_id'=>$data[$i]);
                             $this->posnic->posnic_update($values,$where);
                         }                        
            }     }
            
       redirect('suppliers_x_items');
                         
            
        }
        }
        if($this->input->post('cancel')){
            redirect('suppliers_x_items');
        }
    }
    
    function get_item_details(){
       $q= addslashes($_REQUEST['term']);
                $where=array('code'=>$q);
                $name=$this->posnic->posnic_like('items',$where,'code');
                $dis=  $this->posnic->posnic_like('items',$where,'name');
                $id= $this->posnic->posnic_like('items',$where,'guid');
                $j=0;
                $data=array();
                 for($i=0;$i<count($name);$i++)
                            {                                
                                $data[$j] = array(
                                          'label' =>$name[$i]  ,
                                          'desc' =>$dis[$i],                                          
                                          'id'=>$id[$i]
                                );			
                                        $j++;                                
                        }
        echo json_encode($data);
    }
     
     function get_item_details_for_view($iid){
        if ($iid=="pos") return;
            $this->load->model('purchase');     
            $id=urldecode($iid);
            $where=array('code'=>$id);
            $data=$this->posnic->posnic_one_array_module_where('items',$where);
           foreach ($data as $value){ 
            echo "  <table> <tr><td >Name  </td><td >Description</td><td >Cost</td><td >Price</td><td > MRF</td></tr><tr><td><input type=text value=$value[name] class=items_div disabled ></td><td ><input type=text value =$value[description] class=items_div style=width:100px disabled ></td><td ><input type=text value =$value[cost_price] class=items_div disabled ></td><td ><input type=text value =$value[selling_price] class=items_div disabled ></td><td ><input type=text value= $value[mrf] class=items_div  disabled ></td></tr></table>";
            
            
        }
     }
     function deactive_supplier($guid){
         
     }
   
    
}
?>