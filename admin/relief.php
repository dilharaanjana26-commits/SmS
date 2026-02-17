<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/security.php';
require_login('admin'); verify_csrf(); $schoolId=current_school_id();

function suggest_relief(int $schoolId, string $day, int $classId, int $periodNo, int $absentTeacherId): array {
    $pdo=db();
    $c=$pdo->prepare('SELECT section FROM classes WHERE school_id=? AND id=?');$c->execute([$schoolId,$classId]);$section=$c->fetchColumn() ?: 'secondary';
    $stmt=$pdo->prepare('SELECT t.id,t.name,t.age_group,t.subjects_text FROM teachers t WHERE t.school_id=? AND t.active=1 AND t.id<>?');
    $stmt->execute([$schoolId,$absentTeacherId]);$all=$stmt->fetchAll();
    $ok=[];
    foreach($all as $t){
        $freeQ=$pdo->prepare("SELECT COUNT(*) FROM timetable_entries WHERE school_id=? AND timetable_type='teacher' AND owner_id=? AND day_of_week=?");
        $freeQ->execute([$schoolId,$t['id'],$day]);$busy=(int)$freeQ->fetchColumn(); $free=8-$busy;
        if($free<2) continue;
        $doubleQ=$pdo->prepare("SELECT COUNT(*) FROM timetable_entries WHERE school_id=? AND timetable_type='teacher' AND owner_id=? AND class_id=? AND day_of_week=?");
        $doubleQ->execute([$schoolId,$t['id'],$classId,$day]); if((int)$doubleQ->fetchColumn()>=2) continue;
        $english=stripos($t['subjects_text'],'english')!==false;
        if($t['age_group']==='primary' && in_array($section,['secondary','al'],true) && !$english) continue;
        $periodBusy=$pdo->prepare("SELECT COUNT(*) FROM timetable_entries WHERE school_id=? AND timetable_type='teacher' AND owner_id=? AND day_of_week=? AND period_no=?");
        $periodBusy->execute([$schoolId,$t['id'],$day,$periodNo]); if((int)$periodBusy->fetchColumn()>0) continue;
        $ok[]=$t;
    }
    return $ok;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
 if(($_POST['action']??'')==='assign'){
   db()->prepare("INSERT INTO relief_assignments (school_id,absence_id,date,period_no,class_id,relief_teacher_id,status,created_at) VALUES (?,?,?,?,?,?,?,NOW())")
   ->execute([$schoolId,(int)$_POST['absence_id'],$_POST['date'],(int)$_POST['period_no'],(int)$_POST['class_id'],(int)$_POST['relief_teacher_id'],$_POST['status']]);
   flash('success','Relief assigned');
 }
}
$abs=db()->prepare('SELECT a.*,t.name teacher FROM absences a JOIN teachers t ON t.id=a.teacher_id AND t.school_id=a.school_id WHERE a.school_id=? ORDER BY a.date DESC LIMIT 10');$abs->execute([$schoolId]);$abs=$abs->fetchAll();
$selected=(int)($_GET['absence_id']??($abs[0]['id']??0));
$suggestions=[];$target=[];
if($selected){
  $q=db()->prepare('SELECT * FROM absences WHERE school_id=? AND id=?');$q->execute([$schoolId,$selected]);$a=$q->fetch();
  if($a){
   $te=db()->prepare("SELECT * FROM timetable_entries WHERE school_id=? AND timetable_type='teacher' AND owner_id=? AND day_of_week=?");
   $day=date('D',strtotime($a['date']));$te->execute([$schoolId,$a['teacher_id'],$day]);$target=$te->fetchAll();
   foreach($target as $row){$suggestions[$row['id']]=suggest_relief($schoolId,$day,(int)$row['class_id'],(int)$row['period_no'],(int)$a['teacher_id']);}
  }
}
include __DIR__ . '/../views/layout/header.php'; include __DIR__ . '/../views/layout/sidebar.php'; include __DIR__ . '/../views/layout/topbar.php';
?>
<div class="card p-3"><h5>Relief Suggestions</h5>
<form method="get" class="mb-3"><input type="hidden" name="route" value="admin/relief"><select name="absence_id" class="form-select" onchange="this.form.submit()"><?php foreach($abs as $a):?><option value="<?=$a['id']?>" <?=$selected===$a['id']?'selected':''?>><?=e($a['date'].' - '.$a['teacher'])?></option><?php endforeach;?></select></form>
<table class="table"><thead><tr><th>Period</th><th>Class</th><th>Suggested Teachers</th></tr></thead><tbody>
<?php foreach($target as $r):?><tr><td><?=$r['period_no']?></td><td><?=$r['class_id']?></td><td>
<form method="post" class="d-flex gap-2"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="assign"><input type="hidden" name="absence_id" value="<?=$selected?>"><input type="hidden" name="date" value="<?=e($a['date']??date('Y-m-d'))?>"><input type="hidden" name="period_no" value="<?=$r['period_no']?>"><input type="hidden" name="class_id" value="<?=$r['class_id']?>">
<select name="relief_teacher_id" class="form-select"><?php foreach($suggestions[$r['id']] ?? [] as $s):?><option value="<?=$s['id']?>"><?=e($s['name'])?></option><?php endforeach;?></select>
<select name="status" class="form-select"><option value="auto">auto</option><option value="manual">manual</option></select><button class="btn btn-primary">Assign</button></form>
</td></tr><?php endforeach;?></tbody></table></div>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>
