<?PHP

class load{

	public function test()
    {
	   
        print "ok";
	
    }	



}


class Data_Base{
	   
	   public static $loadaa='';
	   public $FileName='';

	public function index()
    {
	   

	
    }	
	
	
    public function DB($sql)
    {
	  
	
        include("conf/DB_Load.php"); 
	
	    $sql=$sql;
	
	    mysql_query("SET NAMES 'utf8'"); 
	
	
	    $selectA=mysql_query($sql,$loction);
	    if (!$selectA) die("執行 SQL: $sql 命令失敗");
	
        @$row=mysql_num_rows($selectA);	         //如果語法是 [插入] 或 [更新]時  會有報怨    請加上@
	    @$field=mysql_num_fields($selectA);
	  

		
	    
	    $field_name_arr=array();
	    $value_arr=array();
		
		for($fx=0 ; $fx<$field ; $fx++)
		{
			  $property=mysql_fetch_field($selectA);    //全部取完後 再跑一次會沒值可讀
		      $field_name_arr[$fx]=$property->name;		
		}
		
		
		
		
		for($ax=0 ; $ax<$row ; $ax++)
		{
	        $value=mysql_fetch_assoc($selectA);
			
	        for($a=0 ; $a<$field ; $a++)
	        {
			   
			   $value_arr[$ax][$field_name_arr[$a]]= $value[$field_name_arr[$a]];
			
			}
		}
		
		
        mysql_close($loction);		
		
		
		
		/*
        收尋迴傳陣列用處 : 像A類 和 B類 之間的[連結表單] , 當讀取[連結表單]要篩選出AB類的值 又要想讓[AB類排順序] 用這會滿方便的
	    
		1.篩選要排順序的[A類陣列]    , 再將A類 跟 [連結表單]做比對  比對到的index位子指派到新陣列里 , 再將sort($arr);遞增排列 , 
		
		這些就是[A類陣列]的index  
		
		2.如果 有用到分頁的話 請用 $value_H[$order_arr[$index]]['ProductID']   ,  別用:[ mysql_data_seek($selectA,$index); ]

		****而且連結也不用一直開關開關
		*/
		
		return $value_arr;
    }
	
	
	
	
	
	
	



	
    public function insert_sql($insert_table,$insert_arr)
    {
       $name='';
	   $value='';
	
       $insert_arr_row=count($insert_arr);
	   
	   for($ax=1 ; $ax<=$insert_arr_row ; $ax++)
	   {
			
			if( $ax==$insert_arr_row )       //最後一筆的話
			{
	            $name.=$insert_arr[$ax][0]; 
	            $value.="'".$insert_arr[$ax][1]."'";
	        }
			else
			{
	            $name.=$insert_arr[$ax][0].',';
	            $value.="'".$insert_arr[$ax][1]."'".",";			
			}
	   }
	   
	
       $sqlA="insert into $insert_table($name)";
       $sqlB="values($value)";
	   $sql=$sqlA." ".$sqlB;
	   
	   
	   return $sql;
	  
    }	
	
	
	


	
	
	
	

	
    public function insert_sqlX($insert_table,$insert_arr)  //這是為了能動態插入陣列     
    {
       $name='';
	   $value='';
	
       $insert_arr_row=count($insert_arr[0]);   //記得從第一個元素內判斷有幾個 
	   
	   for($ax=0 ; $ax<$insert_arr_row ; $ax++)
	   {
			
			if( $ax==($insert_arr_row-1) )       //最後一筆的話
			{
	            $name.=$insert_arr[0][$ax]; 
	            $value.="'".$insert_arr[1][$ax]."'";
	        }
			else
			{
	            $name.=$insert_arr[0][$ax].',';
	            $value.="'".$insert_arr[1][$ax]."'".",";			
			}
	   }
	   
	
       $sqlA="insert into $insert_table($name)";
       $sqlB="values($value)";
	   $sql=$sqlA." ".$sqlB;
	   
	   
	   return $sql;
	  
    }	
	
	
	
		
	
	
	
	
	
	
	
	
    public function model($file_name)
    {	
        $file_name=$file_name.".php";
	    include_once($file_name);
	}
	
	
	
	
	
    public function view($file_name,$data)
    {	
	
	    // 一但呼叫view() 拉過去[不知道拉到檔哪個地方] ,  $value因為都在同一個函式里  所以include_once($file_name); 的檔讀的到
	    $data=$data;  
        

	    foreach($data as $key => $valueX) {   //$key 取key名     //$valueX 請不用取 $value  我下面會用到
		   
		  $$key=$valueX;                      //把key名 變成變數
		  
	    }		
		
		/*
		

        foreach ($todo_list as $item):

        endforeach;		
		
		*/
		
        $file_name=$file_name.".php";
	    include_once($file_name);
	}
	
	
	
	
	
	
	
	
	//[-------------數值三位數一逗點--------------]
    function money_comma($value)      
    {
        $AA=$value;   //需要是字串  $AA[0] 才讀的到
        $money='';
        $AA_arr=array();
        $AA_arr_ok=array();
        $row=strlen($AA);

 
        for($a=0 ; $a<$row ; $a++){
	
	       $AA_arr[]=$AA[$a];
        }
        $AA_arr=array_reverse($AA_arr);  //顛倒陣列內的元素    回傳顛倒後的陣列 



        for($aa=0 , $ax=1 ; $aa<count($AA_arr) ; $aa++ , $ax++){
	
	       if($ax%3==0){
	        $AA_arr_ok[]=$AA_arr[$aa];
	        $AA_arr_ok[]=',';
	       }
	       else{
		
	        $AA_arr_ok[]=$AA_arr[$aa];
	       }
        }
        $AA_arr_ok=array_reverse($AA_arr_ok);  



        for($ay=0 ; $ay<count($AA_arr_ok) ; $ay++ ){
	
	      $money.=$AA_arr_ok[$ay];
	
        }

        return $money;
    }

	
	
	
	
	
	
	
	
	
}


?>