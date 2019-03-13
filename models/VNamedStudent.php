<?

/**
 * Data model for the view of students joined with names.
 * Don't need the individual tables here because we won't be updating.
 *
 *
 * @author Matt Holden
 */
 class VNamedStudent extends Model3 {

 	/*
 	View create code:
 	
 	CREATE VIEW `v_named_students` AS select `s`.`generatedid` AS `generatedid`,`s`.`ID` AS `ID`,`s`.`address` AS `address`,`s`.`city` AS `city`,`s`.`county` AS `county`,`s`.`state` AS `state`,`s`.`zip` AS `zip`,`n`.`first_name` AS `first_name`,`n`.`last_name` AS `last_name`,`n`.`birthdate` AS `birthdate` from (`students` `s` join `names` `n` on((`s`.`ID` = `n`.`ID`)));
 	*/

 	/**
 	 * Construct the model
 	 */
 	 public function __construct() {
 	 	parent::__construct("v_named_students", "generatedid", false);
 	 	$this->addString("ID")
 	 	     ->addString("address")
 	 	     ->addString("city")
 	 	     ->addString("county")
 	 	     ->addString("state")
 	 	     ->addString("zip")
 	 	     ->addString("first_name")
 	 	     ->addString("last_name")
 	 	     ->addTimestamp("birthdate");
 	 
 	 }
 }