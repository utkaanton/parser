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

        private function connect_db(){
            $host='127.0.0.1';
            $db = 'lotsdb';
            $user = 'root';
            $password = 'root';
            $port = '3306';
        
            try {
                $dsn = "mysql:host=$host;port=$port;dbname=$db;";
                
                $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            
                if ($pdo) {
                   return $pdo;
                }
            } 
            catch (PDOException $e) {
                die($e->getMessage());
            } 
        }

        public function add_to_db(){//добавление записи в БД
            $pdo = $this->connect_db();
            if($this->check_repeat($pdo)){
                try {
                    $sql = "INSERT INTO lots(number_tarid,number_lot,link_lot,start_price,date_event,status_traid) values (?,?,?,?,?,?);";
                    $STH = $pdo->prepare($sql);
                    $STH->execute(array($this->number_tarid,$this->number_lot,$this->link,$this->start_price,$this->date_event,$this->status));
                    echo "Запись добавлена в базу";   
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

    function get_page2(){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'http://www.arbitat.ru/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "ctl00%24ctl00%24BodyScripts%24BodyScripts%24scripts=ctl00%24ctl00%24MainContent%24ContentPlaceHolderMiddle%24UpdatePanel2%7Cctl00%24ctl00%24MainContent%24ContentPlaceHolderMiddle%24PurchasesSearchResult%24ctl01%24ctl02&__EVENTTARGET=ctl00%24ctl00%24MainContent%24ContentPlaceHolderMiddle%24PurchasesSearchResult%24ctl01%24ctl02&__EVENTARGUMENT=&__CVIEWSTATE=7VpbcBPXGdautbLkC3LACEqIvDhugCBkSbYxIgZGyDi44eLEQKadzOystUfyltWu2F35koeWSy7thAmZTC9MJylN0j52xjg4mADOS186fVk9dKadzrRp2jR56EOf%2Bpr%2B%2F9ldSZYlYxLcvEQeH5895z%2F%2F%2Bf7r%2Bc%2FKXzDBINcSj%2FXvTyQG%2BvoGglKIzZYbloG2jbas07LBDkkKMVmJ5YLBEBtuPisb8oRCJiskwDLkDbcf13Ja0RwTc%2BSMrnBMb5dnUpLYZlxldzcEq9dIIS%2FbhCu7mDKxNzi0x9PCeHCj9rRORJOcMYiO%2FPy9heKEImd6l2H1083ZcNtRXdf0E8QwYHtu8InHZxKxROIp3nrTmrcWrJvWYumStcBbi9bd0lXe%2Bshasu6Urli34O%2B8NYdz8HwVEDRRuE1cT0MWHwH9BXs90Le64i2TjAVJEFbwrKjIkmjKmvq0rhULHAdKklVY107n20%2BpaUUmqglt5hyB8SBd3VThlEW6VmHHqEnyaa2ommxTyOdYbLO3I5GMxuNRRMrHBw%2FEYyFXTSqZNnqnZDLdG4%2FHew9b7wH6pdKr1j2Q5R7ogUqyxJcuwuhFmJuz5kuXeesOPMJDlLfehd4F630%2B3h%2Fbl%2FgdN1Q4NGQURJU3zFmFHOzOaqq515BfJAfi8cJM9yHrF6CTe9aHpculC8D0Xuk16zZfumx9BEyAN6q39Hrpx7Dzbd76DfB%2BGfey5nnrWtT6aZQCgZ8bQDJnLZQuRfjSqwiZpxwW7flbYIY5Cn%2BxegGVCO0K8lyyYYORXuaR%2BxKIuQi07jJXQh5WvgYP0LmJRIDbZjSHPBYAHvJ8FXoXrQ%2BBG3Iv%2FQhY3MBHfhdlvAAE6D0wsvsAj3qkroEE3Y5W7%2BEzICm9Bnq5DKtx4HY3T9na4C7YXkj3BkSLsM09qiequltgi%2BuOEhep1yIHMB8uv%2BNihL3mUfzLuDnoCbmBDUAJZRwfUnVTtb2PgqOVXP0uOT6OCqAAMVTu0cF7uBxNgFxLr6ASh3rRExr6Qwz9gd81aZoF40Bvr6ZI0QlRPadrZjRLJJ0YRd2I6sVeJ1yfh4DQpqOiUZg5PDp8MDV49GhfOpGIDQ8mh4%2BkUsnk0f74vuTw4EAyPZjen9zNO9vvXdUf%2Bb7Sj0qXoi7U3sKh9hbJDSkInFi8Ejj7DwzUD5xY71uM9Z6jmyXrLqitHD3gejxVyl06tuC6JbUZDmKPL%2Fs%2FuhV0F8ADXGuDyx5w%2FJGPlz2X%2F%2BSln%2FHxvuTgvojtmnesDyhv8KK3YN1dB8x8lOf%2F0PQAYXkRuD9wWKIblF6iyO6iq8JSCE6eetgi9Q2Uc4F61bzrdaAe6nVL1LNgoBLM6ONVqqd8XCEd56NhdAWFXQQ0c9ZtGxEq862o9U6Up4GHqrxZoxHY%2BS5v%2FTxq%2FSoa4SnFHEaIdQtRAxmNYifCK1kiFqPahpB2TLEbFUStWrpCQ54GC0LCtftifF8yHukD8PM0JG9C3qR5AEVepCkWIwyEXXQyQo3ReevX1jvwc73WO9lKWu%2BPxpKoojgfjx3oG6jnnbFE74j1S354WBvnaRaagxBfsEFav4X8fZ233oDdb9CTCwj4RB%2BGPw3p0lXIfxcAC%2B5xmQE3ejBWrqffsLMpJls8TjAJgdCV1DfvhkzpdfTmBXA3%2B7C94poa04kt%2FiO1xUfWOVtrz8MuHy1HynVEtlJjsJVu87JyA05R72kyY3KPWG%2BiEio%2BsCixgWDt%2Bc24NZGPdp%2FI0hrHnzaMtCIaBteZgUDTNYXPaEoxr54s5ieIHvYKO8aPsGzIH%2FaqYp5wuYypxGI9dntClNWjM2BvSYT6KQXVTU9h0h5Ia4oiFgzSM1bUM5OiQYxxIkIvrcsm0WWxZ8qdOK6ZgqKZ9n6Ce5iFA3lx5jhRc%2BYkx8bj4YCmniOzkFVV7rBYNLW8NkV2kSkoNCK8OSkbET4ej%2FA7R1WzL7EzwqtFRVne7tzrDO8Ot1BeBUjbBvc9Ocvv2uE%2BHgPkCtG%2FJOPdvE7Moq7yWVExyFOSXUZ2%2BTmOYC3XFWBZx5442OzoG4dhoIX7wXqp9rRsKkSw3qYJzs7t5dhFz7U13tXKeROxWExyatg6CM8%2FLIRjom7OCshezJgnwa%2BElCKLxgho8ZSeE1XI9jpFDaCaBhCTtxGmwsPFJKuqYF0DXb3t5lOqpVs0h%2BAI6omNJ%2BzLw%2F8D0blCQYAz7zr8NEbEJCXW1wjQNebhItJJTjZggEgpCYsfQ7DegDODHpr0bHi%2FfE445dUqyJsSAwP2nWp9lbksKI5g8VYs2K5n%2FaS6JKEeh%2Fb1N4I0va6QRk%2BedB1wWalUcbxAHWCQV1qc4bp5nIL3A3h1XcAXnL6TxCvFCMUc72r7ykm7q319U3VrvVTd1sgFLjLrosYsQFs9WS%2FTrBcKpn774o0oQ8sNny7qOlEzs2XTv7I%2BoEdV2ZRFZUyXMzZurBydcgjKU0gA9A6Kh4x9X6Q%2B0Q8%2BkWrgE%2F1gqWGSkfOicn%2BveGENXrF2hiv8YgNVLqiw7Bv0lUYdZ%2BmoF5a07upwXngMi6Z4RCuqUk4C0izLsCzbFPJ2cD67gOOC8VgEXDiegBb%2B9OU6uA1QpcINmKZRuHhwHCWhE8teT3A%2BexXM7IbLNV7L75RL9wX70oZ3Crx4fOBe%2BcAOfTkJy9Q6fs6Cy7z70FzmiAjnuqyeni0QgbrKiKbnBbg22PewOReg%2B%2BamXGRfrQv9NqyzHWtBYjc2gq%2Bva7IbN0WzaIwOC9Y79hUCb6Ru9dTMbbGuOS9a8O5Xqc27OHwrRl892t1H6hXp2B3a42llPcFQZ9iPrvMMmTVCnnBnmob3uKabgB6dXtZULjHlwoKCL%2Br2VZqPI8vm4NceDm%2BETEl0FWJX1OEghL6RWAp5GI%2FH8wV88C9%2B2lhovjui5ca1rBl9nkxEEU0qk4GtI%2FxZWAUADsL1NxqLxiJ8uqhABJGDKimaugjRNUbveYD%2BtHaOqAcHY%2F1QZmZiyf5MVurP9nG4T0999tEysuNQcXiBMHRMNCaJhI97BAN0YPe3VA9PiUqRGKfRzFurx4uqfL5IztLZbw3LGXyJKeqzR0AxewRQz6SJS5qaPE3bx2ehwMlH0TEIpTOiKV0XZ5FRvclj7mpm1VnUZKAJG5SFCXDQevF51Q2RwCeAK%2BYNTsA3IX5hytY6rPf7Az6YZ51fr7cBtzIGpG45ronSCNTemt7sGLAVDJTW8gVRJ7oficdhIy%2F6nM%2FWmMfT5OG4Vv%2BjLnPbzqlpWFFe%2BO9n9x%2BmQjZ7kB46fmwCKC0VFn5X0rRi0wZNh8%2BhCbRjswGadtaLdKvtijza4DZBn0bVrNYqyDlVAwKwrF%2FQClQDYFbeYfK0ok2IivwifY0dTVcWMo%2BvRnHKZhQIojTe%2F0CIrIUc3buZuqQgePz41OF3jEUFpKJ2BJwhP6XwI4Wno9UZ9G2CZlNKUbRpIlVlUsPXiWRjNQmpo20F%2F7Yt0DxfHWRpO1c%2BlAjGHcL1mFfiFyXYWn6yQ47QzAu5rLtmwrEenUtNQBrLkFAtiTEizxCpa%2BWwpqeLBtQVzxaJPru13vx3NFndVDOBLze21YxpE9%2BH6KHe31MzVRBlvTJEJsUpWdP51YjwqhFaQWBiAt5SM6yT80UZble1GA3Qy6N1xkZVwxRBRyvpXyS1CjI0cBZHtQhpc828Cd5FzFq1mdNEPAdpecUWJnhhZ80Y9XV6eDCsl2G8kJIYr6eJwU%2BkYZ6vo0904T33Oxies5WVh3oP6bsa0I%2BDnlBepNnbiOa8EnWPxOhJzRxVcRWNyifuB%2BM01RvSMl1OUjgDhfHsONYPbmY4pikS0TEUfVvp0QpaCmAIt3D%2FgmzyQMpZnlZY56wObHUb7p%2FA8UG0V58h9w9gcz%2BlrlyK6SwQdnj4uqB5rLqGitbWUNzHsM0alVwfqCewA7fqhqYzvi8SH4zE90fiycg%2BLKeTkSRDDxNXO1TzmJNamL%2FAzqj95epj%2FgxDqEJq0j9BBxVBD7PdrlhPQrNtmVgTldw8Osz8EVZ87HB24O2FprbK9%2BJ5ct%2BjCU9FX57mqcC0rPYljqdHh5szdqpuzwtYDzqZHGRr8vvrc6wisxUBnx9SDww%2FcGSgIdqxg3n1DJZSEPHI0Iuy3i8M8NT24h4BSoE6a0sZBslPKLO2UzF%2BXy%2FdwmZ0iqZjqocYNM%2FkjYymK%2FJE5QTrx%2FNrLSfYxOCgOJAZ2BdP9vWT2P4khzZ9ci3C30fy1vBm%2B95t1tTnT61Sn%2FMSMTINinReNDLhDocn%2FifCqCqRmWy446ysm0VRqXyJ%2Fnumy8d2hjfb98xxmuerZ8MBXG4%2FtYaDiG8Y9tXlAlSBRuK%2FHV9L4d8oQ9knVAUg1sFYQHYIJv4TBX1dnDKA9%2FpfCDx4I2DW80rwzZ3gmztB%2BU5QlrLqTuDxuDeD5XeCR1fJKvRuEKqfVSo3hPJutIjfucZo3IwL6FnkE0QMxRVh2Q5VrEJFx8S%2BURBzOZ3koIIcKarU%2B9oEiWRFsBx%2BhRmgkYuUfkFWM0pRIkFBAjeHB7sANwLAUJROqQqwhmJAlACd%2FSQq0%2BKscaYAI6RTgIybJZAtJSj0CZgcsATKFfVGQXJkAAS2UCFh0swreGGBBJsyTV2eKJrECNcfHrMrdqM5PSmqOSJtH3qO2CU17HPonCAcETPnZDU3IhNF6q6eHANc8kwtyWNDw7YWqJC1swwDsct4aTJmoJRmaBH9eAMjPVelcLTtrgZ0qVpTrFo3O3ahZRB%2Bvr2qizj1pITkm5xAOIrvYp0Xs9TnNrmNh%2FscA2YN8qys%2BXCE%2BwyWr1nM%2BnWjbxsF8ulqZa6jg5UMXCCeAHKhJffa9NOgekcuLfRTKVc73cbDfAL8P3cOaObv0PnMrVtxIfM3ePrUna4M%2F9WpZGt2%2BNrKzm2OtLTsbJWYzj2e0NAeD%2Fg3fYvbntNlKaWYtsLwXW7VZAtOVmZgqVeiE20rvu2qOyp9yY2qZrYv%2F3qFX8EFQdH%2F2KDkG%2Bp8G9NgXHpwcb6SAuoos4J7Bxgvp6YJFr68qk3rYsH5YgnfA5yW86RWlOrx6m34OpzqKs0WJHLfjfnluBtisAUKBzAKaT7luuuelwa9hMpS1ISjqIFr4DD5ykZqWYmcfB2Wb2%2Bsf9IAWM24JG1lubAguG%2F6nMQ2phkmnmBw4AlCiOVGar%2BBcU7UHufvmCJmiH0fPCFLcELUfgUDJyjkGu7ph%2FRNDtddzei0VjgB1ZLzlAe2%2BBgMStx295%2BFrcUXVv531f8A&__VIEWSTATE=&__SCROLLPOSITIONX=0&__SCROLLPOSITIONY=0&__EVENTVALIDATION=%2FwEdAC6vVXD1oYELeveMr0vHCmYPxMGvS2gF0sIlDMntuxXtESJpbrgauTgNdBNdrVqIwDIKN4e7PZiSCOChdLVmerZKzBtLNosu8SXmswchpytWcKUr325VYmRWaw4AAoyalh6CrJcT5%2B2DhT7Xt%2BfmqcZCayg7clSzJgSXOtIOGH2Is9xJX9uo5sKqwUbBtCD5a3eUwzBB2L0kTvpeUOmLIZoaIr3oyH3PgwgB8jvJ9slQmw875beVJfb8NZ0JwKTeslzKtxT60fkeRRzRi9x%2FnMkR%2B6x8G77zvb2T%2BWAOnjTZbfJl1rUPcelZJjDFW6kGIPOplwfBuOqUMhOzruBeqjE18YolLIINhSJvqjSg7we08dcm3aV%2BgzLB8ZJiWvfj2F%2FXCwvPe5UOK0h1bWVks1SbJiWsDgoePJChvxY3452Cs5lACXIS3sBzWIyVYy974yqoKuw5SVCY3LY1WWkotNPnsqdOt2uloCF9G3mq1UuUtwdw10mFxgjZVzEMDxUPciv6OjutbfhZC%2Fa4rO8EjuHjAGyEdXRQjGlUULW%2BW3cgy%2FwWuZwwD%2FJifOJ%2F2S9GcIEP5HNfaXqYRNVIFjH9SBQEfIdLlliO1q7GAES3rY12khyqpjgF%2FMMzeURfehtC2L1bF9cPx6roDFW36RlSoXKYDHYIf5%2BbMW0irqLd9EMT8K5D1CQGstEygXOM0M%2FFOnLy3Z54y7iTmQxHO3PYXpCliUuG6TxIJ6wljZxNYMaUpFHepmMBSblWiF0hlPGAW07Cy2E%2BtN9EnW7P4257PnrDZillZhwLHHcU2qCdYlyT3RHT10omRQ7NUZe85y1DVdbWaAy%2FG0S9Y86nz4fmGna4Sp2lFliRrR70cB5y8CqMcccZy3MvYDRBSWS94XXIP7GzdUQv63IMPYjaorKQL8HhGhKJrFGN7C3e8oPysDSt2NaCefcIVuRGZpRvtLTLrCf%2FnEohy2cDHein3ZnBLKJvdK%2Bvrnr14BXp7l8O4EcCGcGDUClf6O3LVBSyRoHA%2FQs%3D&ctl00%24ctl00%24LeftContentLogin%24ctl00%24Login1%24UserName=&ctl00%24ctl00%24LeftContentLogin%24ctl00%24Login1%24Password=&ctl00%24ctl00%24LeftContentSideMenu%24mSideMenu%24extAccordionMenu_AccordionExtender_ClientState=0&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24vPurchaseLot_lotNumber_%D0%BB%D0%BE%D1%82%D0%B0=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24vPurchaseLot_purchaseNumber_%D1%82%D0%BE%D1%80%D0%B3%D0%B0=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24vPurchaseLot_lotTitle_%D0%9D%D0%B0%D0%B8%D0%BC%D0%B5%D0%BD%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D0%B5%D0%BB%D0%BE%D1%82%D0%B0=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24vPurchaseLot_fullTitle_%D0%9D%D0%B0%D0%B8%D0%BC%D0%B5%D0%BD%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D0%B5%D1%82%D0%BE%D1%80%D0%B3%D0%B0=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24Party_contactName_AliasFullOrganizerTitle=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24vPurchaseLot_InitialPrice_%D0%9D%D0%B0%D1%87%D0%B0%D0%BB%D1%8C%D0%BD%D0%B0%D1%8F%D1%86%D0%B5%D0%BD%D0%B0%D0%BE%D1%82%D1%80%D1%83%D0%B1=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24Party_inn_%D0%98%D0%9D%D0%9D%D0%BE%D1%80%D0%B3%D0%B0%D0%BD%D0%B8%D0%B7%D0%B0%D1%82%D0%BE%D1%80%D0%B0=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24vPurchaseLot_bargainTypeID_%D0%A2%D0%B8%D0%BF%D1%82%D0%BE%D1%80%D0%B3%D0%BE%D0%B2%24ddlBargainType=10%2C11%2C12%2C111%2C13&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24Party_kpp_%D0%9A%D0%9F%D0%9F%D0%BE%D1%80%D0%B3%D0%B0%D0%BD%D0%B8%D0%B7%D0%B0%D1%82%D0%BE%D1%80%D0%B0=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24BargainType_PriceForm_%D0%A4%D0%BE%D1%80%D0%BC%D0%B0%D0%BF%D1%80%D0%B5%D0%B4%D1%81%D1%82%D0%B0%D0%B2%D0%BB%D0%B5%D0%BD%D0%B8%D1%8F%D0%BF%D1%80%D0%B5%D0%B4%D0%BB%D0%BE%D0%B6%D0%B5%D0%BD%D0%B8%D0%B9%D0%BE%D1%86%D0%B5%D0%BD%D0%B5=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24Party_registeredAddress_%D0%90%D0%B4%D1%80%D0%B5%D1%81%D1%80%D0%B5%D0%B3%D0%B8%D1%81%D1%82%D1%80%D0%B0%D1%86%D0%B8%D0%B8%D0%BE%D1%80%D0%B3%D0%B0%D0%BD%D0%B8%D0%B7%D0%B0%D1%82%D0%BE%D1%80%D0%B0=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24vPurchaseLot_purchaseStatusID_%D0%A1%D1%82%D0%B0%D1%82%D1%83%D1%81=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24vPurchaseLot_BankruptName_%D0%94%D0%BE%D0%BB%D0%B6%D0%BD%D0%B8%D0%BA=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24vPurchaseLot_BankruptINN_%D0%98%D0%9D%D0%9D%D0%B4%D0%BE%D0%BB%D0%B6%D0%BD%D0%B8%D0%BA%D0%B0=&__ASYNCPOST=true&");
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = 'Accept: */*';
        $headers[] = 'Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7';
        $headers[] = 'Cache-Control: no-cache';
        $headers[] = 'Connection: keep-alive';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
        $headers[] = 'Cookie: __ddg1_=BwN3ysfPBuKtHrYbFo0l; _ym_uid=1670313530113039652; _ym_d=1670313530; ASP.NET_SessionId=vo02clvqfvmx15z30nafuqim; _ym_isad=2';
        $headers[] = 'Origin: http://www.arbitat.ru';
        $headers[] = 'Pragma: no-cache';
        $headers[] = 'Referer: http://www.arbitat.ru/';
        $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36';
        $headers[] = 'X-Microsoftajax: Delta=true';
        $headers[] = 'X-Requested-With: XMLHttpRequest';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return $result;
    }
/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////    
    $html= file_get_contents('http://www.arbitat.ru/');
    
    $obj_1 = new Lot(serch_data($html,3));
    $obj_2 = new Lot(serch_data($html,4));
    $obj_3 = new Lot(serch_data($html,6));
    
    $obj_1->show_data();
    $obj_2->show_data();
    $obj_3->show_data();
    
    echo get_page2();
    $obj_1->add_to_db();
    $obj_2->add_to_db();
    $obj_3->add_to_db();
?>