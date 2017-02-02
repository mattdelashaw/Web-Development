<?php 
	require_once('/var/location.php');
	$dev=gen_dude_where_are_we($_SERVER['SERVER_ADDR'], $_SERVER['REQUEST_URI']);
	require ($_SERVER['DOCUMENT_ROOT']."$dev/includes/top_functions.php");
	require_once($_SERVER['DOCUMENT_ROOT']."$dev/access/connection.php");
	require_once($_SERVER['DOCUMENT_ROOT']."$dev/includes/gen_functions.php");
	gen_sessionCheck($db);
	gen_check_page_permission_super_admin($_SESSION['user_type']);
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	
	//		this is borrowed from the public information pool that is the internet to parse encoded sessions that have "|"
	function unserializesession($data) {
		$vars=preg_split('/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff^|]*)\|/',
				  $data,-1,PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		for($i=0; $i<count($vars); $i++) $result[$vars[$i++]]=unserialize($vars[$i]);
		return $result;
	}
	
	$now = time();
	$infoArr['logins'] = array();
	$sess = session_save_path()."/"; 
	if(is_dir($sess)){
		if ($dh = opendir($sess)){
			while (($file = readdir($dh)) !== false){
				if(preg_match("/sess/", $file)){
					$file = $sess.$file;
					$read = fopen($file, "r");
					$detail = fstat($read);
					if($detail[7]>0){			//file size
						$encode = file_get_contents($file);
						$temp = explode("|", $encode);
						if($temp[0]=="user"){
							$log = unserializesession($encode);
							$infoArr['logins'][$log['timeframe']['start_time']] = $log;
							$infoArr['logins'][$log['timeframe']['start_time']]['file'] = $file;
						}
					}
					fclose($read);
				}
			}
			closedir($dh);
		}
	}
	krsort($infoArr['logins']);
	$encodedInfoArr = json_encode($infoArr);
	
	include "includes/header.php";
?>
<script type="text/javascript">
	$(document).ready(function(){
		$("#kill-button").click(function(){
			$.ajax({
				type: 'POST',
           	 	url: 'actions/kill_sessions_action.php',
				data: <?php echo $encodedInfoArr; ?>,
           	 	success: function() {
					alert("sessions ended");
					location.reload();
				},
				error: function(err){
					console.log(JSON.stringify(err));
				}
	        });
	    });
	});
	
	function singleUser(userArr){
		$.ajax({
			type: 'POST',
           	url: 'actions/kill_single_session_action.php',
			data: {'id': userArr},
           	success: function(data) {
				alert("session ended");
				location.reload();
			},
			error: function(err){
				console.log(JSON.stringify(err));
			}
	    });
	}
</script>
        <div id="page-wrapper">

            <div class="container-fluid">

                <!-- Page Heading -->
                <div class="row">
                    <div class="col-lg-12">
                        <h1 class="page-header">
                            Current Active Users <small>Management Page</small>
                        </h1>
                    </div>
                </div>
                <!-- /.row -->

				<!-- Color Row -->
				<div class="row">
					<div class="col-lg-12">
						<br/>
						<img class="img-responsive" src="img/topColor1920x20.jpg" alt="">
					</div>
				</div>
				<!-- /.row -->
				<br/>				
				<button id="kill-button" class='btn btn-red' >Kill Sessions</button>
				<br/>
				
				<div class="row">
                    <div class="col-lg-12">
					<h3>Logged In</h3>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped" id="main_table">
                                <thead>
                                    <tr>
										<th>Active</th>
                                        <th>User</th>
                                        <th>Last Activity Time</th>
										<th>Currently Viewing</th>
										<th>Browser</th>
									</tr>
                                </thead>
                                <tbody>
								<?php
								if(! empty($infoArr)){
										foreach($infoArr['logins'] as $number=>$array){
											print "
												<tr>
													<td width='15'><a href='#' id='active' onclick='singleUser(".json_encode($array)."); 'title='kill session'> <i class='fa fa-lg fa-power-off ";
													if($array['timeframe']['cutoff_time']>0){
														print "info-green";
													}else{
														print "info-red";
													}
													print"'></i></a></td>
													<td>".gen_html_special_chars($array['username'])."</td>
													<td>".gen_html_special_chars(date('h:i:s A', $array['timeframe']['start_time']))."</td>
													<td>".gen_html_special_chars($array['last_visited'][0])."</td>
													<td>".gen_html_special_chars($array['browser'])."</td>
												</tr>
											";
										}
									}
								?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

				<!-- /.row -->

            </div>
            <!-- /.container-fluid -->

        </div>
        <!-- /#page-wrapper -->

<?php include "includes/footer.php"; ?>
