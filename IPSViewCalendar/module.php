<?php

declare(strict_types=1);

class IPSViewCalendar extends IPSModule
{
	// -------------------------------------------------------------------------
	public function Create()
	{
		//Never delete this line!
		parent::Create();

		$this->RegisterPropertyString  ('Appointments',        '[]');
	}

	// -------------------------------------------------------------------------
	public function Destroy()
	{
		//Never delete this line!
		parent::Destroy();
	}

	// -------------------------------------------------------------------------
	public function ApplyChanges()
	{
		//Never delete this line!
		parent::ApplyChanges();
	}

	// -------------------------------------------------------------------------
	public function UpdateAppointments($appointments) {
		$maxID = 0;
		foreach ($appointments as $appointment) {
			$maxID = max($maxID, $appointment['ID']);
		}

		$newAppointments = [];
		foreach ($appointments as $appointment) {
			if ($appointment['ID'] == 0) {
				$appointment['ID'] = ++$maxID;
			}
			$newAppointments[] = $appointment;
		}
		$this->UpdateFormField('Appointments', 'values', json_encode($newAppointments));
	}

	// -------------------------------------------------------------------------
	public function DeleteAppointment($id) {
		$appointments     = json_decode($this->ReadPropertyString('Appointments'), true);

		$newAppointments = [];
		foreach ($appointments as $appointment) {
			if ($appointment['ID'] != $id) {
				$newAppointments[] = $appointment;
			}
	   }
	   IPS_SetProperty($this->InstanceID, 'Appointments',  json_encode($newAppointments));
	   IPS_ApplyChanges($this->InstanceID);
	}

	// -------------------------------------------------------------------------
	public function CreateAppointment($name, $year1, $month1, $day1, $hour1, $minute1, $year2, $month2, $day2, $hour2, $minute2, $isAllDay, $color, $description) {
		$appointments     = json_decode($this->ReadPropertyString('Appointments'), true);

		$maxID = 0;
		foreach ($appointments as $appointment) {
			$maxID = max($maxID, $appointment['ID']);
		}
		$id = $maxID+1;
		$this->UpdateAppointment($id, $name, 
		                         $year1, $month1, $day1, $hour1, $minute1, 
		                         $year2, $month2, $day2, $hour2, $minute2, 
								 $isAllDay, $color, $description);

		return $id;
	}
	
	// -------------------------------------------------------------------------
	public function UpdateAppointment($id, $name, $year1, $month1, $day1, $hour1, $minute1, $year2, $month2, $day2, $hour2, $minute2, $isAllDay, $color, $description) {
		$appointments     = json_decode($this->ReadPropertyString('Appointments'), true);

		$newAppointments = [];
		foreach ($appointments as $appointment) {
			if ($appointment['ID'] != $id) {
				$newAppointments[] = $appointment;
			}
	   	}

	   	$newAppointments[] = [
			'ID'          => $id,
			'Name'        => $name,
			'StartDate'   => "{\"year\": $year1, \"month\": $month1, \"day\": $day1 }",
			'StartTime'   => "{\"hour\": $hour1, \"minute\": $minute1}",
			'EndDate'     => "{\"year\": $year2, \"month\": $month2, \"day\": $day2 }",
			'EndTime'     => "{\"hour\": $hour2, \"minute\": $minute2}",
			'AllDay'      => $isAllDay,
			'Color'       => $color,
			'Description' => $description
	   	];

	   	IPS_SetProperty($this->InstanceID, 'Appointments',  json_encode($newAppointments));
		IPS_ApplyChanges($this->InstanceID);
	}	
}