<?php
use CRM_Vedacivirulesextra_ExtensionUtil as E;

/**
 * Class for CiviRules Membership Update Status Action. 
 */
class CRM_Civirules_Action_MembershipUpdateStatus extends CRM_Civirules_Action {

  
  /**
   *  {@inherit}
   * 
   * @param CRM_Civirules_Trigger $trigger
   * @param CRM_Civirules_BAO_Rule $rule
   * @return bool
   */
  public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
    return $trigger->doesProvideEntity('Membership');
  }

 /**
   * Method processAction to execute the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   *
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $membership = $triggerData->getEntityData("Membership");
    $actionParams = $this->getActionParameters();
    $params = [];
    if (!empty($actionParams['unset_status_override']) && !empty($membership['is_override'])) {
      $params['is_override'] = 0;
    }
    switch ($actionParams['update_operation']) {
      case 'fixed_value':
        if (!empty($actionParams['membership_status'])) {
          $params['status_id'] = $actionParams['membership_status'];
        }
        break;
      case 'calculate':
        $status_id = $this->getCalcStatusId($membership['id']);
        if ($status_id && $status_id != $membership['status_id']) {
          $params['status_id'] = $status_id;
        }
        break;
    }
    if ($params && !empty($membership['id'])) {
      $params['id'] = $membership['id'];
      $result = civicrm_api3('Membership', 'create', $params);
    }
  }

  /**
 * Returns a user friendly text explaining the condition params
 *
 * @return string
 * @access public
 */
public function userFriendlyConditionParams() {
  $params = $this->getActionParameters();
  $op = $params['update_operation'];
  if ($op == 'calculate') {
    $label = E::ts('Calculate membership status.');
  }
  elseif ($op == 'fixed_value') {
    $statusId = $params['membership_status'];
    $statusDetails = CRM_Member_BAO_MembershipStatus::getMembershipStatus($statusId);
    $name = $statusDetails['name'];
    $label = E::ts('Set membership status to %1.', [1 => $name]);
  }
  else {
    $label = E::ts('Not setting membership status');
  }
  if ($params['unset_status_override']) {
    $label .= ' Unset status override.';
  }
  return $label;
}

protected function getCalcStatusId($membershipId) {
  $result = civicrm_api3('MembershipStatus', 'calc', [
    'membership_id' => $membershipId
  ]);
  return !empty($result['id']) ? $result['id'] : NULL;   
 }

  /**
   * Returns a redirect url to extra data input from the user after adding a action
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleActionId
   * @return bool|string
   * @access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/admin/civirulesextra/action/membershipupdatestatus', 'rule_action_id=' . $ruleActionId);
  }
}