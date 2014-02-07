<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>校園RFID系統｜活動簽到</title>
    @include('import',array('target'=>'活動簽到'))
    <script>
        function load_activity_data(){
            $('.checkin_history').removeClass('hidden');
            $('.checkin_history tbody').text('');
            var aid = $('#select_activity').val();
            if(aid != ''){
                $.post('{{url()}}/activity/data',{aid:aid},function(data){
                    $('.checkin_num').text('0');
                    $('#checkin_alert').addClass('hidden');
                    $('#activity_load_alert').removeClass('hidden');
                    data = JSON.parse(data);
                    $('#activity_name').text('活動名稱：' + data.activity_name);
                    $('#activity_date').text('活動日期：' + $.datepicker.formatDate("yy/mm/dd",new Date(data.activity_date)).replace('1970/01/01',''));
                    $('#activity_organize').text('主辦單位：' + data.activity_organize);
                    $('#activity_type').text('簽到類型：' + data.activity_type.replace('no_check','無需身分驗證').replace('strict_check','需嚴格身分驗證').replace('only_prompt','僅需提示身份是否符合'));
                })    
            }
        }

        function isJson(str) {
            try {
                JSON.parse(str);
            } catch (e) {
                return false;
            }
            return true;
        }
        
        function checkin(){
            $.post('{{url()}}/activity/check',$('#checkin_form').serialize(),function(data){
                $('.checkin-data').addClass('hidden');
                $('#checkin_alert').removeClass('hidden');
                $('.checkin_title').text('');
                $('#activity_load_alert').addClass('hidden');
                if(data == '資格不符合，簽到失敗'){
                    $('.checkin_title').text(data);
                }else{
                    $('.checkin_num').text(parseInt($('.checkin_num').text()) + 1);
                    if(isJson(data)){
                        data = JSON.parse(data);
                        if(data.message == undefined){
                            data.message = '簽到成功';
                        }
                        $('.checkin-data').removeClass('hidden');
                        $('.checkin_title').text(data.message);

                        $('#checkin_name').text('姓名：' + htmlspecialchars(data.name));
                        $('#checkin_job').text('職務（票種）：' + htmlspecialchars(data.job));
                        $('#checkin_department').text('科系：' + htmlspecialchars(data.department));
                        $('#checkin_studentid').text('學號：' + htmlspecialchars(data.student_id));
                        $('.checkin_history tbody').prepend('<tr><td>' + htmlspecialchars(data.student_id) + '</td><td>' + new Date().getHours() + ":" + new Date().getMinutes() + '</td></tr>')
                    }else{
                        $('.checkin_title').text(data + '簽到成功');
                        $('.checkin_history tbody').prepend('<tr><td>' + htmlspecialchars(data) + '</td><td>' + new Date().getHours() + ":" + new Date().getMinutes() + '</td></tr>')
                    }
                }
                $('#stu_card').val('');
            })
        }
        $(document).ready(function(){
            $('#select_activity').change(function(){
                load_activity_data();
            });
            
            $('#checkin_form').submit(function(e){
                e.preventDefault();
                if(($('#stu_card').val().length == 9)){
                    checkin();
                }
            });
        })
    </script>
</head>

<body>
    <div id="wrapper">
        @include('menu')
        <div id="page-wrapper">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-3 col-sm-4">
                            <h1>活動簽到</h1>
                        </div>
                        <div class="col-md-9 hidden-xs col-sm-8">
                            <div class="btn-group quick-btn pull-right">
                                <a href="{{url()}}/activity/create" class="btn btn-default">建立活動</a>
                                <a href="{{url()}}/namelist/create" class="btn btn-default">建立名冊</a>
                                <a href="{{url()}}/activity/view" class="btn btn-default">檢視活動</a>
                                <a href="{{url()}}/namelist/view" class="btn btn-default">檢視名冊</a>              
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <ul class="breadcrumb">
                                <li><a href="{{url()}}/activity/check">活動簽到</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div> <!-- row -->
            <div class="row">
                <div class="col-md-6">
                    {{Form::open(array('id'=>'checkin_form'))}}
                        <div class="form-group">
                            <label for="select_activity">選擇活動</label>
                            {{Form::select('activity',$activity_list,$default,array('class'=>'form-control','id'=>'select_activity'))}}
                        </div>
                        <div class="form-group">
                            <label for="stu_card">學生證</label>
                            <input type="text" class="form-control" id="stu_card" name="student_id" placeholder="請刷學生證">
                        </div>
                    {{Form::close()}}
                    <div class="alert alert-success {{$default==null?'hidden':''}}" id="activity_load_alert">
                        <h3 class="text-center">活動資訊載入成功</h3>
                        <br>
                        <ul class="list-group">
                            @if ($default != '')
                                <?php
                                    // 為了讓簽到類型可以顯示中文
                                    $default_data->activity_type = str_replace(array('no_check','strict_check','only_prompt'), array('無需身分驗證','需嚴格身分驗證','僅需提示身份是否符合'), $default_data->activity_type)
                                ?>
                                <li class="list-group-item" id="activity_name">活動名稱：{{{$default_data->activity_name}}}</li>
                                <li class="list-group-item" id="activity_type">簽到類型：{{{$default_data->activity_type}}}</li>
                                <li class="list-group-item" id="activity_date">活動時間：{{{date('Y/m/d',strtotime($default_data->activity_date))}}}</li>
                                <li class="list-group-item" id="activity_organize">主辦單位：{{{$default_data->activity_organize}}}</li>
                            @else
                                <li class="list-group-item" id="activity_name">活動名稱：</li>
                                <li class="list-group-item" id="activity_type">簽到類型：</li>
                                <li class="list-group-item" id="activity_date">活動時間：</li>
                                <li class="list-group-item" id="activity_organize">主辦單位：</li>
                            @endif
                        </ul>
                    </div>

                    <div class="alert alert-success hidden" id="checkin_alert">
                        <h3 class="text-center checkin_title"></h3>
                        <br>
                        <ul class="list-group checkin-data hidden">
                            <li class="list-group-item" id="checkin_name"></li>
                            <li class="list-group-item" id="checkin_studentid"></li>
                            <li class="list-group-item" id="checkin_department"></li>
                            <li class="list-group-item" id="checkin_job"></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="pull-right">
                        <div class="btn-group">
                            <a class="btn btn-default">已簽到人數<span class="checkin_num">0</span>人</a>
                        </div>
                    </div>
                    <table class="table table-hover checkin_history {{$default==null?'hidden':''}}">
                        <thead>
                            <tr>
                                <td>學號</td>
                                <td>時間</td>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div> <!-- page-wrapper -->
    </div>
</body>
</html>