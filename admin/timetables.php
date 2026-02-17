<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/security.php';
require_login('admin');
verify_csrf();
$schoolId = current_school_id();

function recalc_special_times(string $firstStart): array {
    $fixedInterval = new DateTime('10:30');
    $first = new DateTime($firstStart);
    $preSlots = ['P1','Register','P2','P3','P4'];
    $postSlots = ['P5','P6','P7','P8'];
    $preMinutes = max(40, (int)(($fixedInterval->getTimestamp()-$first->getTimestamp())/60));
    $step1 = intdiv($preMinutes, count($preSlots));
    $times = []; $t = clone $first;
    foreach($preSlots as $i=>$slot){$times[$slot]=$t->format('H:i');$t->modify("+{$step1} minutes");}
    $times['Interval']='10:30';
    $after = new DateTime('10:50'); $step2 = 40;
    foreach($postSlots as $slot){$times[$slot]=$after->format('H:i');$after->modify("+{$step2} minutes");}
    return $times;
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (($_POST['action'] ?? '') === 'save_entry') {
        db()->prepare('REPLACE INTO timetable_entries (id,school_id,timetable_type,owner_id,day_of_week,period_no,class_id,teacher_id,subject,room) VALUES (NULL,?,?,?,?,?,?,?,?,?)')
        ->execute([$schoolId,$_POST['timetable_type'],(int)$_POST['owner_id'],$_POST['day_of_week'],(int)$_POST['period_no'],(int)$_POST['class_id'],(int)$_POST['teacher_id'],clean_input($_POST['subject']),clean_input($_POST['room'])]);
        flash('success','Timetable entry saved');
    }
    if (($_POST['action'] ?? '') === 'special_time') {
        $times = recalc_special_times($_POST['first_period_start']);
        $order = [['period',1,'P1'],['register',0,'Register'],['period',2,'P2'],['period',3,'P3'],['period',4,'P4'],['interval',0,'Interval'],['period',5,'P5'],['period',6,'P6'],['period',7,'P7'],['period',8,'P8']];
        foreach ($order as $row) {
            [$label,$p,$key]=$row;
            db()->prepare('INSERT INTO period_times (school_id,day_type,period_no,start_time,label) VALUES (?,?,?,?,?)')->execute([$schoolId,'special',$p,$times[$key],$label]);
        }
        flash('success','Special day times recalculated with fixed interval 10:30');
    }
}
$type = $_GET['type'] ?? 'class';
$ownerId = (int)($_GET['owner_id'] ?? 0);

if ($type === 'teacher') {
    $entries = db()->prepare('SELECT te.*, c.name AS class_label FROM timetable_entries te LEFT JOIN classes c ON c.id = te.class_id AND c.school_id = te.school_id WHERE te.school_id=? AND te.timetable_type=? AND te.owner_id=? ORDER BY te.day_of_week, te.period_no, te.id');
    $entries->execute([$schoolId,$type,$ownerId]);
    $map=[];
    foreach($entries as $e){
        $map[$e['day_of_week']][$e['period_no']][]=$e;
    }
} else {
    $entries = db()->prepare('SELECT * FROM timetable_entries WHERE school_id=? AND timetable_type=? AND owner_id=?');
    $entries->execute([$schoolId,$type,$ownerId]);
    $map=[];
    foreach($entries as $e){
        $map[$e['day_of_week']][$e['period_no']]=$e;
    }
}

$classes=db()->prepare('SELECT id,name FROM classes WHERE school_id=?');$classes->execute([$schoolId]);$classes=$classes->fetchAll();
$teachers=db()->prepare('SELECT id,name,age_group,subjects_text FROM teachers WHERE school_id=? AND active=1');$teachers->execute([$schoolId]);$teachers=$teachers->fetchAll();
include __DIR__ . '/../views/layout/header.php'; include __DIR__ . '/../views/layout/sidebar.php'; include __DIR__ . '/../views/layout/topbar.php';
$days=['Mon','Tue','Wed','Thu','Fri'];
?>
<div class="card p-3 mb-3"><form class="row g-2" method="get"><input type="hidden" name="route" value="admin/timetables">
<div class="col-md-3"><select name="type" class="form-select"><option value="class" <?=$type==='class'?'selected':''?>>Class</option><option value="teacher" <?=$type==='teacher'?'selected':''?>>Teacher</option></select></div>
<div class="col-md-4"><select name="owner_id" class="form-select"><?php foreach(($type==='class'?$classes:$teachers) as $o): ?><option value="<?=$o['id']?>" <?=$ownerId===$o['id']?'selected':''?>><?=e($o['name'])?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><button class="btn btn-primary">Load Grid</button></div></form></div>
<div class="card p-3 mb-3"><h6>Special Day First Period Start</h6><form method="post" class="row g-2"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="special_time"><div class="col-md-3"><input type="time" class="form-control" name="first_period_start" value="07:45"></div><div class="col-md-2"><button class="btn btn-warning">Recalculate</button></div></form></div>
<div class="card p-3"><table class="table table-bordered"><thead><tr><th>Day/Period</th><?php for($p=1;$p<=8;$p++):?><th>P<?=$p?></th><?php endfor;?></tr></thead><tbody>
<?php foreach($days as $d): ?><tr><th><?=$d?></th><?php for($p=1;$p<=8;$p++): ?>
<?php
$cellEntries = $type==='teacher' ? ($map[$d][$p] ?? []) : [];
$cell = $type==='teacher' ? ($cellEntries[0] ?? null) : ($map[$d][$p] ?? null);
$classLabels = [];
if ($type==='teacher' && !empty($cellEntries)) {
    foreach ($cellEntries as $entry) {
        if (!empty($entry['class_label'])) {
            $classLabels[] = $entry['class_label'];
        }
    }
    $classLabels = array_values(array_unique($classLabels));
}
?>
<td class="<?= $type==='teacher' && empty($cellEntries)?'free-period':'' ?>">
<?php if($type==='teacher'): ?>
    <?php if(!empty($classLabels)): ?>
        <div class="d-flex flex-wrap gap-1"><?php foreach($classLabels as $label): ?><span class="badge bg-primary"><?=e($label)?></span><?php endforeach; ?></div>
    <?php else: ?>
        <small>Free</small>
    <?php endif; ?>
<?php else: ?>
    <small><?= $cell?e($cell['subject']):'Free' ?></small>
<?php endif; ?>
<form method="post" class="mt-1"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save_entry"><input type="hidden" name="timetable_type" value="<?=$type?>"><input type="hidden" name="owner_id" value="<?=$ownerId?>"><input type="hidden" name="day_of_week" value="<?=$d?>"><input type="hidden" name="period_no" value="<?=$p?>">
<select name="class_id" class="form-select form-select-sm mb-1"><?php foreach($classes as $c):?><option value="<?=$c['id']?>" <?=($cell['class_id']??0)==$c['id']?'selected':''?>><?=e($c['name'])?></option><?php endforeach;?></select>
<select name="teacher_id" class="form-select form-select-sm mb-1"><?php foreach($teachers as $t):?><option value="<?=$t['id']?>" <?=($cell['teacher_id']??0)==$t['id']?'selected':''?>><?=e($t['name'])?></option><?php endforeach;?></select>
<input name="subject" class="form-control form-control-sm mb-1" value="<?=e($cell['subject']??'')?>" placeholder="Subject"><input name="room" class="form-control form-control-sm mb-1" value="<?=e($cell['room']??'')?>" placeholder="Room"><button class="btn btn-sm btn-outline-primary">Save</button></form>
</td><?php endfor; ?></tr><?php endforeach; ?></tbody></table></div>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>
