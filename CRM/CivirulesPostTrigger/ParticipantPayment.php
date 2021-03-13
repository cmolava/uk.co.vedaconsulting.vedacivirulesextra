<?php

/**
 * Class for CiviRules post trigger handling - ParticipantPayment.
 *
 * @license AGPL-3.0
 */
class CRM_CivirulesPostTrigger_ParticipantPayment extends CRM_CivirulesPostTrigger_EntityPaymentBase {

  /**
   * Return an array of Entity DAO classes for additional entities, keyed by
   * Entity name.
   *
   * This is used to create Entity Definitions.
   * Sub-classes should override.
   *
   * @see CRM_CivirulesPostTrigger_EntityPaymentBase::getAdditionalEntities()
   */
  protected function getAdditionalEntityClasses() {
    return [
      'Contribution' => 'CRM_Contribution_DAO_Contribution',
      'Membership' => 'CRM_Member_DAO_Membership',
    ];
  }

  /**
   * Return the name of the DAO Class. If a dao class does not exist return an empty value.
   *
   * @return string
   */
  protected function getDaoClassName() {
    return 'CRM_Event_DAO_ParticipantPayment';
  }

  /**
   *
   */
  public function getTriggerDescription() {
    return \CRM_Vedacivirulesextra_ExtensionUtil::ts('Provides Participant and Contribution for Conditions and Actions.', ['1' => $this->op]);
  }

  /**
   *
   */
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

}
