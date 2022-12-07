<?php
    //header('Content-type: text/plain; charset=utf-8');
   
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
    class Lot{
        private $number_tarid, $number_lot, $link, $start_price, $date_event, $status;

        function __construct($data_lot){
            $this->number_tarid=$data_lot[0]; 
            $this->number_lot=$data_lot[1];
            $this->link=$data_lot[2];
            $this->start_price=$data_lot[3];
            $this->date_event=$data_lot[4];
            $this->status=$data_lot[5];
        }

        public function show_data(){
            echo '<table>';
            echo '<tr><th>№</th><th>Номер лота</th><th>Ссылка</th><th>Cтартовая цена</th><th>Дата проведения</th><th>Статус</th></tr>';
            echo '<tr><td>',$this->number_lot,'</td><td>',$this->number_tarid,'</td><td>',$this->link,'</td><td>',$this->start_price,'</td><td>',$this->date_event,'</td><td>',$this->status,'</td></tr>';
            echo '</table>';
        }

        public function add_to_db(){//добавление записи в БД
            $host='127.0.0.1';
            $db = 'lotsdb';
            $user = 'root';
            $password = 'root';
            $port = '3306';
        
            try {
                $dsn = "mysql:host=$host;port=$port;dbname=$db;";
                
                $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            
                if ($pdo) {
                   
                }
            } 
            catch (PDOException $e) {
                die($e->getMessage());
            } 
            if($this->check_repeat($pdo)){
                try {
                    $sql = "INSERT INTO lots(number_tarid,number_lot,link_lot,start_price,date_event,status_traid) values (?,?,?,?,?,?);";
                    $STH = $pdo->prepare($sql);
                    $STH->execute(array($this->number_tarid,$this->number_lot,$this->link,$this->start_price,$this->date_event,$this->status));   
                }
                catch (PDOException $e) {
                    echo "Database error: " . $e->getMessage();
                }   
            }
            else echo "Запись уже существует в базе";
            
        }

        private function check_repeat ($pdo){//ф. проверки на наличие лота в БД
            $STH = $pdo->prepare("SELECT number_tarid FROM lots");  
            $STH->execute();
            $res=$STH->fetchAll(PDO::FETCH_COLUMN);
            for($i=0;$i<count($res);$i=$i+1){
                if($res[$i]==$this->number_tarid) return false;
            }
            return true;
        }
    }

    function time_valid($date_event){//ф.приводит дату в корректный формат
        $day='';
        $month='';
        $year='';
        $time='';
        $date_event=ltrim ($date_event);
        for($i=0;$i<17;$i=$i+1){
            if($i<2) $day=$day.$date_event[$i];
            else if ($i<5 && $date_event[$i]!='.')$month=$month.$date_event[$i];
            else if ($i<10 && $date_event[$i]!='.')$year=$year.$date_event[$i];
            else if ($i>11 && $i<17 && $date_event[$i]!='.')$time=$time.$date_event[$i];
        }
        return($year.'-'.$month.'-'.$day.' '.$time);
    }

    function price_valid($price){//ф.приводит цену в корректный формат
        $res='';
        for($i=0;$i<strlen($price);$i=$i+1){
            if($price[$i]=='0')$res=$res.$price[$i];
            else if ($price[$i]=='1')$res=$res.$price[$i];
            else if ($price[$i]=='2')$res=$res.$price[$i];
            else if ($price[$i]=='3')$res=$res.$price[$i];
            else if ($price[$i]=='4')$res=$res.$price[$i];
            else if ($price[$i]=='5')$res=$res.$price[$i];
            else if ($price[$i]=='6')$res=$res.$price[$i];
            else if ($price[$i]=='7')$res=$res.$price[$i];
            else if ($price[$i]=='8')$res=$res.$price[$i];
            else if ($price[$i]=='9')$res=$res.$price[$i];
            else if ($price[$i]==',')break;
        }
        return $res;
    }

    function serch_data($html,$position_lot){//парсер
        preg_match_all('#<tr class="gridRow">(.+?)</tr>#su', $html, $list_lot);

        preg_match_all('#<a[^>]*?>(.*?)</a>#si', $list_lot[1][$position_lot-1], $number_tarid);//[1][0]
        preg_match_all('#<a[^>]*?>(.*?)</a>#si', $list_lot[1][$position_lot-1], $number_lot);//[1][2]
        preg_match_all('#<a.*?href=["\'](.*?)["\'].*?>#i', $list_lot[1][$position_lot-1], $link);//[1][2]
        preg_match_all('#<td[^>]*?>(.*?)</td>#su', $list_lot[1][$position_lot-1], $start_price);//[1][4]
        preg_match_all('#<td[^>]*?>(.*?)</td>#si', $list_lot[1][$position_lot-1], $date_event);//[1][6]
        preg_match_all('#<td[^>]*?>(.*?)</td>#si', $list_lot[1][$position_lot-1], $status);//[1][8]

        $link[1][2]=preg_replace("/^/", 'http://www.arbitat.ru/', $link[1][2]);
        $date_event[1][6]=time_valid($date_event[1][6]);
        $start_price[1][4]=price_valid($start_price[1][4]);
        
 
        $data_lot = [$number_tarid[1][0],$number_lot[1][2],$link[1][2],$start_price[1][4],$date_event[1][6],$status[1][8]];
        return $data_lot;
    }
/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////    
    $html= file_get_contents('http://www.arbitat.ru/');
    
    
    $obj_1 = new Lot(serch_data($html,3));
    $obj_2 = new Lot(serch_data($html,4));
    $obj_3 = new Lot(serch_data($html,6));
    $obj_1->show_data();
    $obj_1->add_to_db();
    $obj_2->show_data();
    $obj_2->add_to_db();
    $obj_3->show_data();
    $obj_3->add_to_db();
?>