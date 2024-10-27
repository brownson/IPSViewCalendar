<?php

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
    
    return true;
	}

	// -------------------------------------------------------------------------
	public function CreateAppointment($name, 
	                                  $year1, $month1, $day1, $hour1, $minute1, 
									  $year2, $month2, $day2, $hour2, $minute2, 
									  $isAllDay, $color, $description, 
									  $recurrenceDateType , $recurrenceDateInterval, $recurrenceDateDay, $recurrenceDateDayInterval) {
		$appointments     = json_decode($this->ReadPropertyString('Appointments'), true);

		$maxID = 0;
		foreach ($appointments as $appointment) {
			$maxID = max($maxID, $appointment['ID']);
		}
		$id = $maxID+1;
		$this->UpdateAppointment($id, $name, 
		                         $year1, $month1, $day1, $hour1, $minute1, 
		                         $year2, $month2, $day2, $hour2, $minute2, 
								 $isAllDay, $color, $description,
								 $recurrenceDateType , $recurrenceDateInterval, $recurrenceDateDay, $recurrenceDateDayInterval);

		return $id;
	}
	
	// -------------------------------------------------------------------------
	public function UpdateAppointment($id, $name, 
	                                  $year1, $month1, $day1, $hour1, $minute1, 
									  $year2, $month2, $day2, $hour2, $minute2, 
									  $isAllDay, $color, $description,
									  $recurrenceDateType , $recurrenceDateInterval, $recurrenceDateDay, $recurrenceDateDayInterval
									) {
		$this->SendDebug("UpdateAppointment", "Update Appointment $id", 0);
		$appointments     = json_decode($this->ReadPropertyString('Appointments'), true);
		$newAppointments = [];
		$oldAppointment;
		foreach ($appointments as $appointment) {
			if ($appointment['ID'] != $id) {
				$newAppointments[] = $appointment;
			} else {
				$oldAppointment = $appointment;
			}
	   	}

	   	$newAppointment = [
			'ID'                        => $id,
			'Name'                      => $name,
			'StartDate'                 => "{\"year\": $year1, \"month\": $month1, \"day\": $day1 }",
			'StartTime'                 => "{\"hour\": $hour1, \"minute\": $minute1, \"second\": 0}",
			'EndDate'                   => "{\"year\": $year2, \"month\": $month2, \"day\": $day2 }",
			'EndTime'                   => "{\"hour\": $hour2, \"minute\": $minute2, \"second\": 0}",
			'AllDay'                    => $isAllDay,
			'Color'                     => $color,
			'ScriptID'                  => $oldAppointment['ScriptID'],
			'Description'               => $description,
			'RecurrenceDateType'        => $recurrenceDateType , 
			'RecurrenceDateInterval'    => $recurrenceDateInterval, 
			'RecurrenceDateDay'         => $recurrenceDateDay, 
			'RecurrenceDateDayInterval' => $recurrenceDateDayInterval
	   	];
		
		$newAppointments[] = $newAppointment;

		IPS_SetProperty($this->InstanceID, 'Appointments',  json_encode($newAppointments));
		IPS_ApplyChanges($this->InstanceID);

		$scriptID = $newAppointment['ScriptID'];
		$eventID  = 0;

		if ($scriptID > 0) {
			$eventID = IPS_GetObjectIDByIdent("event_$id", $scriptID);
		}

		if ($scriptID == 0 && $eventID != 0) {
			$this->SendDebug("UpdateAppointment", "Delete Event $eventID", 0);
			IPS_DeleteEvent($eventID);
		} else if ($scriptID != 0 && $eventID == 0) {
			$eventID = IPS_CreateEvent(1);
			$this->SendDebug("UpdateAppointment", "Created Event $eventID", 0);
			IPS_SetIdent($eventID, "event_$id");
			IPS_SetParent($eventID, $this->InstanceID);
			IPS_SetName($eventID, 'Event for Appointment '.$id);
			IPS_SetPosition($eventID, $id);
		}

		if ($eventID != 0) {
			$this->SendDebug("UpdateAppointment", "Update Event $eventID", 0);
			$eventStart = mktime($hour1, $minute1, 0, $month1, $day1, $year1);
			$eventEnd = mktime($hour2, $minute2, 0, $month2, $day2, $year2);

			IPS_SetEventCyclic($eventID,  $recurrenceDateType, $recurrenceDateInterval, $recurrenceDateDay, $recurrenceDateDayInterval, 0, 0);
			IPS_SetEventCyclicDateBounds($eventID, $eventStart, null);
			IPS_SetEventCyclicTimeBounds($eventID, $eventStart, $eventEnd);
			IPS_SetEventAction($EreignisID, "{7938A5A2-0981-5FE0-BE6C-8AA610D654EB}", []);
			IPS_SetParent($eventID, $scriptID);
			IPS_SetEventActive($eventID, true);
		}


		return true;
	}
}
