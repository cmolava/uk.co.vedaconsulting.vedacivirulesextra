<?php

use CRM_Vedacivirulesextra_ExtensionUtil as E;

/**
 * Class for CiviRules post trigger handling - ParticipantPayment
 *
 * @license AGPL-3.0
 */

class CRM_CivirulesPostTrigger_ParticipantPayment extends CRM_Civirules_Trigger_Post {

  /**
   * To prevent rules being triggered multiple times, we keep a 
   * list of the rule ids  and entity ids.
   */
  static protected $triggeredRules = [];

  /**
   * Returns an array of entities on which the trigger reacts
   *
   * @return CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function reactOnEntity() {
    return new CRM_Civirules_TriggerData_EntityDefinition($this->objectName, $this->objectName, $this->getDaoClassName(), 'ParticipantPayment');
  }

  /**
   * Return the name of the DAO Class. If a dao class does not exist return an empty value
   *
   * @return string
   */
  protected function getDaoClassName() {
    return 'CRM_Event_DAO_ParticipantPayment';
  }

  public function getAdditionalEntities() {
    $entity_definitions = parent::getAdditionalEntities();
    $definitions = [
      ['Participant', 'Participant', 'CRM_Event_DAO_Participant' ],
      ['Event', 'Event', 'CRM_Event_DAO_Event' ],
      ['Contribution', 'Contribution', 'CRM_Contribute_DAO_Contribution' ],
    ];
    $entity_definitions = [];
    foreach ($definitions as $definition) {
      $entity_definitions[] = new CRM_Civirules_TriggerData_EntityDefinition($definition[0], $definition[1], $definition[2]);
    }
    return $entity_definitions;
  
  }

  public function getProvidedEntities() {
    return parent::getProvidedEntities();
  }

  public function getTriggerDescription() {
    return E::ts('Provides Participant and Contribution for Conditions and Actions.', ['1' => $this->op]);
  }

  public function alterTriggerData(CRM_Civirules_TriggerData_TriggerData &$triggerData) {
    $participant_payment = $triggerData->getEntityData('ParticipantPayment');
    if ($participant_payment['contribution_id']) {
      $contribution = $this->crm('Contribution', 'getsingle', ['id' => $participant_payment['contribution_id']]);
      $triggerData->setEntityData('Contribution', $contribution);
    } 
    if ($participant_payment['participant_id']) {
      $participant = $this->crm('Participant', 'getsingle', ['id' => $participant_payment['participant_id']]);
      $triggerData->setEntityData('Participant', $participant);
      if (!empty($participant['event_id'])) {
        $event = $this->crm('Event', 'getsingle', ['id' => $participant['event_id']]);
        $triggerData->setEntityData('Event', $event);
      }
    } 
  }

  protected function crm($entity, $op, $params) {
    try {
      $result = civicrm_api3($entity, $op, $params);
      return $result;
    }
    catch (Exception $e) {
     //
    }
  }

  /**
   * This is mentioned in the docs but is not invoked anywhere?
   * @see https://docs.civicrm.org/civirules/en/latest/trigger/
   */
  public function checkTrigger() {
  
  }

  /**
   * Check whether a rule has already been triggered with the object in this request.
   * This can be used to avoid duplicate triggers from multiple post hook invocations.
   */
  public function hasAlreadyTriggered($objectId) {
    $ruleId = $this->getRuleId();
    if (empty(self::$triggeredRules[$ruleId][$objectId])) {
      // 
      self::$triggeredRules[$ruleId][$objectId] = 1;
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Trigger a rule for this trigger
   *
   * @param $op
   * @param $objectName
   * @param $objectId
   * @param $objectRef
   */
  public function triggerTrigger($op, $objectName, $objectId, $objectRef) {
    if (!$this->hasAlreadyTriggered($objectId)) {
      return parent::triggerTrigger($op, $objectName, $objectId, $objectRef);
    }
  }

}
