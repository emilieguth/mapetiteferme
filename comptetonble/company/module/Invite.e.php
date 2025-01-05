<?php
namespace company;

class Invite extends InviteElement {


  public function isValid(): bool {

    if($this->empty()) {
      return FALSE;
    }

    $this->expects(['status', 'expiresAt']);

    return (
      $this['status'] === Invite::PENDING and
      $this['expiresAt'] >= currentDate()
    );

  }
}
?>