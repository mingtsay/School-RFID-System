<?php
class Equip extends Eloquent{
    protected $table = 'equip_history';
    protected $guarded = array('bid');
    public $primaryKey = 'student_id';
    public $timestamps = false;
    /*
        已歸還 = returned
        未歸還 = not_return
        無限期 = no_deadline
    */

    public static function getNotReturnEquipList($student_id){
        $student_id = Helper::convert_card_id($student_id);
        $data = self::find($student_id);
        if($data === null){
            return 'false';
        }else{
            $data = $data->whereRaw('type = \'not_return\' or type=\'no_deadline\' and uid = ?',array(Login::getUid()))->get();
            if($data->count() == 0){
                return 'false';
            }else{
                return $data->toJson();    
            }
            
        }
    }
    public static function borrowEquip($student_id, $equip_name, $estimate_return_date){
        $student_id = Helper::convert_card_id($student_id);
        if($estimate_return_date == ''){
            $data = self::create(array(
                'student_id'=>$student_id,
                'equip_name'=>$equip_name,
                'borrow_time'=>date('Y/m/d H:i:s',time()),
                'type'=>'no_deadline',
                'uid'=>Login::getUid()
            ));  
            return $data->student_id;
        }elseif(strtotime($estimate_return_date) < time()){
            return '預計歸還時間不可早於借出時間';
        }else{
            $data = self::create(array(
                'student_id'=>trim($student_id),
                'equip_name'=>trim($equip_name),
                'borrow_time'=>date('Y/m/d H:i:s',time()),
                'estimate_return_time'=>$estimate_return_date,
                'type'=>'not_return',
                'uid'=>Login::getUid()
            ));    
            return $data->student_id;
        }
        
        
    }
    public static function setRecordReturned($bid){
        return self::whereRaw('bid = ?',array($bid))->update(array(
            'type'=>'returned',
            'return_time'=>date('Y/m/d H:i',time())
        ));
    }
    public static function getRecordList($type = 'not_return', $month = 'all', $page=1, $num = 20){
        if($type == 'be_lated'){
            if($month == 'all'){
                return self::whereRaw('type = \'not_return\' and estimate_return_time < now() and uid = ?',array(Login::getUid()))->orderBy('estimate_return_time')->take($num)->skip(($page-1)*$num)->get()->toJson();

            }else{
                return self::whereRaw('type = \'not_return\' and estimate_return_time < now() and month(borrow_time) = ? and uid = ?',array($month,Login::getUid()))->orderBy('estimate_return_time')->take($num)->skip(($page-1)*$num)->get()->toJson();

            }
        }else{
            if($type == 'not_return'){
                if($month == 'all'){
                    return self::whereRaw('return_time is NULL and uid = ?', array(Login::getUid()))->orderBy('borrow_time','DESC')->take($num)->skip(($page-1)*$num)->get()->toJson();

                }else{
                    return self::whereRaw('return_time is NULL and month(borrow_time) = ? and uid = ?', array($month,Login::getUid()))->orderBy('borrow_time','DESC')->take($num)->skip(($page-1)*$num)->get()->toJson();

                }
            }elseif($type == 'returned'){
                if($month == 'all'){
                    return self::whereRaw('type = \'returned\' and uid = ?', array(Login::getUid()))->orderBy('return_time','DESC')->take($num)->skip(($page-1)*$num)->get()->toJson();

                }else{
                    return self::whereRaw('type = \'returned\' and month(borrow_time) = ? and uid = ?', array($month,Login::getUid()))->orderBy('return_time','DESC')->take($num)->skip(($page-1)*$num)->get()->toJson();

                }
            }
            
        }


    }
    public static function getRecordListPageCount($type = 'not_return', $month = 'all',$num = 20){
        if($type == 'be_lated'){
            if($month == 'all'){
                return ceil(self::whereRaw('type = \'not_return\' and estimate_return_time < now() and uid = ?', array(Login::getUid()))->count()/$num);

            }else{
                return ceil(self::whereRaw('type = \'not_return\' and estimate_return_time < now() and month(borrow_time) = ? and uid = ?', array($month, Login::getUid()))->count()/$num);

            }
        }else{
            if($type == 'not_return'){
                if($month == 'all'){
                    return ceil(self::whereRaw('return_time is NULL and uid = ?', array(Login::getUid()))->count()/$num);

                }else{
                    return ceil(self::whereRaw('return_time is NULL and month(borrow_time) = ? and uid = ?', array($month,Login::getUid()))->count()/$num);

                }
            }elseif($type == 'returned'){
                if($month == 'all'){
                    return ceil(self::whereRaw('type = \'returned\' and uid = ?',array(Login::getUid()))->count()/$num);

                }else{
                    return ceil(self::whereRaw('type = \'returned\' and month(borrow_time) = ? and uid = ?',array($month,Login::getUid()))->count()/$num);

                }
            }
            
        }
    }
    
}