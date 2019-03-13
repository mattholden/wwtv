<? 
/* El cheapo autoloader solution for demo app */
require_once(__DIR__."/lib/autoload.php"); 

// In a perfect world this would all be in a controller class; but we're going low-tech in the name
// of speed for this assignment.

$results = [];
$errors = [];

// Define a list of valid fields. Helps with UI definition and hiding ops, and also 
// ensures bad guys can't use a field we don't want to expose.
$fields = [
	"first_name" =>		["label" => "First Name", 	"type" => "string" ],
	"last_name" => 		["label" => "Last Name", 	"type" => "string" ],
	"city" => 			["label" => "City", 		"type" => "string" ],
	"county" => 		["label" => "County", 		"type" => "string" ],
	"state" => 			["label" => "State", 		"type" => "string" ],
	"zip" => 			["label" => "ZIP Code", 	"type" => "string" ],
	"birthdate" => 		["label" => "DOB", 			"type" => "date" ],
	"ID" => 			["label" => "ID", 			"type" => "string" ]
];

$sorts = [ "first_name", "last_name", "city", "county", "state", "zip", "birthdate", "ID"];

$ops = [ 
	0 => 	["op" => "=",   "label" => "Equal", 		"types" => ["string", "int", "date"] ],
	1 => 	["op" => "!=",	"label" => "Not Equal", 	"types" => ["string", "int", "date"] ],
	2 => 	["op" => ">=",	"label" => "Greater/Equal", "types" => ["int", "date"] ],
	3 => 	["op" => ">",	"label" => "Greater", 		"types" => ["int", "date"] ],
	4 => 	["op" => "<=",	"label" => "Less/Equal", 	"types" => ["int", "date"] ],
	5 => 	["op" => "<",	"label" => "Less", 			"types" => ["int", "date"] ],
	6 => 	["op" => "LIKE","label" => "Contains", 		"types" => ["string"] ]
];
	
$field = "ID";
$sort = "ID";
$op = 0;
$value = "";

if ($_SERVER["REQUEST_METHOD"] === "GET") {
	$results = VNamedStudent::batch([],[],"ID");

}

else {
	$field = isset($_POST["field"]) ? $_POST["field"] : null;
	if ($field !== null && !in_array($field, array_keys($fields))) {
		$errors[] = "You searched by an invalid field. Please try again. ;(";
	}
	else {
		// Shouldn't need this due to the array whitelist but never hurts to be extra safe
		$field = Config::getDatabase()->escape($field);
	}

	$value = isset($_POST["value"]) ? $_POST["value"] : null;
	if ($fields[$field]["type"] == "int" && !empty($value)) {
		$value = intval($value);
	}

	$op = isset($_POST["op"]) ? $_POST["op"] : 0;
	if ($op !== null && !in_array($op, array_keys($ops))) {
		$errors[] = "You searched with an invalid operator. Please try again. ;(";
	}	

	$sort = isset($_POST["sort"]) ? $_POST["sort"] : "ID";
	if (!in_array($sort, $sorts)) {
		$errors[] = "You searched with an invalid sort column. Please try again. ;(";
	}	
	else {
		// Shouldn't need this due to the array whitelist but never hurts to be extra safe
		$field = Config::getDatabase()->escape($field);
	}

	if ($field !== null) {
		
		$val = ($op == 6) ? "%".$value."%" : $value;
		$results = VNamedStudent::batch([$field], [$val], $sort, [$ops[$op]["op"]]);
	}
	var_dump($_POST);
}

?>
<!doctype html>
<!-- Website template by freewebsitetemplates.com -->
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Programming Assignment - Matthew Holden</title>
	<link rel="stylesheet" href="css/style.css" type="text/css">
	<link rel="stylesheet" type="text/css" href="css/mobile.css">
	<script src="js/mobile.js" type="text/javascript"></script>
</head>
<body>
	<div id="page">
		<div id="header">
			<div>
				<a href="index.php" class="logo"><img src="images/logo.png" alt="A totally real company for realz."></a>				
			</div>
		</div>
		<div id="body" class="home">
			<div class="header">
				<div>
					<img src="images/satellite.png" alt="Stuff in spaaaaaaaaace!" class="satellite">
					<h1>SPACEFLIGHT TRAINING</h1>
					<h2>GRADING SYSTEM</h2>
					
				</div>
			</div>
			
			<div class="footer">
				<div>
					<ul >
						<li>
							<form method="post">
							<h1>Search Criteria</h1>
							<br/>
							<label for="field">Search Field:</label>

							<?/* TODO: Use "type" arrays in onChange to ensure greater/less ops can't 
							     be used on non-numeric values */?>
							<select name="field" id="field">
								<? foreach ($fields as $f=>$fld): ?>
								<option value="<?= htmlspecialchars($f) ?>" 
										data-type= "<?= htmlspecialchars($fld["type"]) ?>"
										<?= ($f == $field) ? "selected" : "" ?>>
										<?= htmlspecialchars($fld["label"]) ?>
								</option>
								<? endforeach; ?>
							</select>
							<br/>
							<label for="op">Comparison:</label>
							<select name="op" id="op">
								<? foreach ($ops as $o=>$operator): ?>
								<option value="<?= $o ?>" 
										data-types= "<?= implode("|",$operator["types"]) ?>"
										<?= ($o == $op) ? "selected" : "" ?>>
										<?= htmlspecialchars($operator["label"]) ?>
								</option>
								<? endforeach; ?>
							</select>
							<br/>
							<label for="value">Value:</label>
							<input type="text" name="value" id="value" value="<?= $value ?>" />
							<br/>
							
							<label for="sort">Sort by:</label>
							<select name="sort" id="sort">
								<? foreach ($sorts as $s): ?>
								<option value="<?= htmlspecialchars($s) ?>" 
									<?= ($s == $sort) ? "selected" : "" ?>>
									<?= htmlspecialchars($fields[$s]["label"]) ?>
								</option>
								<? endforeach; ?>
							</select>
							<input type="submit" value="Search" />
							</form>
						</li>
					</ul>
				</div>
			</div>
			<div class="footer">
				<div>
					
					<h1>RESULTS</h1>
					<? if (count($results) > 0): ?>
					<table style="border: 1px solid white; color: white;">
						<thead>
							<tr>
								<th>ID</th>
								<th>First</th>
								<th>Last</th>
								<th>DOB</th>
								<th>City</th>
								<th>County</th>
								<th>State</th>
								<th>ZIP</th>
							</tr>
						</thead>
						<tbody>
							<? foreach ($results as $r): ?>
							<tr>
								<td><?= $r->html("ID") ?></td>
								<td><?= $r->html("first_name") ?></td>
								<td><?= $r->html("last_name") ?></td>
								<td><?= date("m/d/Y", strtotime($r->html("birthdate"))) ?></td>
								<td><?= $r->html("city"); ?></td>
								<td><?= $r->html("county") ?></td>
								<td><?= $r->html("state") ?></td>
								<td><?= sprintf("%05d", intval($r->html("zip"))) ?></td>
							</tr>
							<? endforeach; ?>
						</tbody>
					</table>
					<br/>
					<? endif; ?>
					<span style="color: white;"><?= count($results) ?> results found.</span>
				</div>
			</div>
		</div>
		<div id="footer">

			<div class="footnote">
				<div>
					<p>&copy; 2019<?= (date("Y") == 2019 ? "" : " - " . date("Y")) ?> Hoping to Get Hired, Inc.
					<br/>
					Matt Holden - <a href="https://www.mattholden.com" target="_blank">website</a> - <a href="https://www.mattholden.com/2019resume.pdf">resume</a> - <a href="tel:+14073402340">phone</a> - <a href="mailto:matt@mattholden.com">email</a></p>
				</div>
			</div>
		</div>
	</div>
</body>
</html>