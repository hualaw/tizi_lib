<?php
namespace yiduoyun\phone;
/**
 * Autogenerated by Thrift Compiler (0.9.0)
 *
 * DO NOT EDIT UNLESS YOU ARE SURE THAT YOU KNOW WHAT YOU ARE DOING
 *  @generated
 */
use Thrift\Base\TBase;
use Thrift\Type\TType;
use Thrift\Type\TMessageType;
use Thrift\Exception\TException;
use Thrift\Exception\TProtocolException;
use Thrift\Protocol\TProtocol;
use Thrift\Exception\TApplicationException;


interface PhoneServiceIf extends \yiduoyun\ydy303\YdyServiceIf {
  public function add_phone($appname, $uid, $phonenum);
  public function change_phone($appname, $uid, $phonenum);
  public function get_uid($appname, $phonenum);
  public function get_phone($appname, $uid);
}

class PhoneServiceClient extends \yiduoyun\ydy303\YdyServiceClient implements \yiduoyun\phone\PhoneServiceIf {
  public function __construct($input, $output=null) {
    parent::__construct($input, $output);
  }

  public function add_phone($appname, $uid, $phonenum)
  {
    $this->send_add_phone($appname, $uid, $phonenum);
    return $this->recv_add_phone();
  }

  public function send_add_phone($appname, $uid, $phonenum)
  {
    $args = new \yiduoyun\phone\PhoneService_add_phone_args();
    $args->appname = $appname;
    $args->uid = $uid;
    $args->phonenum = $phonenum;
    $bin_accel = ($this->output_ instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'add_phone', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('add_phone', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_add_phone()
  {
    $bin_accel = ($this->input_ instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\yiduoyun\phone\PhoneService_add_phone_result', $this->input_->isStrictRead());
    else
    {
      $rseqid = 0;
      $fname = null;
      $mtype = 0;

      $this->input_->readMessageBegin($fname, $mtype, $rseqid);
      if ($mtype == TMessageType::EXCEPTION) {
        $x = new TApplicationException();
        $x->read($this->input_);
        $this->input_->readMessageEnd();
        throw $x;
      }
      $result = new \yiduoyun\phone\PhoneService_add_phone_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    if ($result->ae !== null) {
      throw $result->ae;
    }
    if ($result->ee !== null) {
      throw $result->ee;
    }
    throw new \Exception("add_phone failed: unknown result");
  }

  public function change_phone($appname, $uid, $phonenum)
  {
    $this->send_change_phone($appname, $uid, $phonenum);
    return $this->recv_change_phone();
  }

  public function send_change_phone($appname, $uid, $phonenum)
  {
    $args = new \yiduoyun\phone\PhoneService_change_phone_args();
    $args->appname = $appname;
    $args->uid = $uid;
    $args->phonenum = $phonenum;
    $bin_accel = ($this->output_ instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'change_phone', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('change_phone', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_change_phone()
  {
    $bin_accel = ($this->input_ instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\yiduoyun\phone\PhoneService_change_phone_result', $this->input_->isStrictRead());
    else
    {
      $rseqid = 0;
      $fname = null;
      $mtype = 0;

      $this->input_->readMessageBegin($fname, $mtype, $rseqid);
      if ($mtype == TMessageType::EXCEPTION) {
        $x = new TApplicationException();
        $x->read($this->input_);
        $this->input_->readMessageEnd();
        throw $x;
      }
      $result = new \yiduoyun\phone\PhoneService_change_phone_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    if ($result->ae !== null) {
      throw $result->ae;
    }
    if ($result->ne !== null) {
      throw $result->ne;
    }
    if ($result->ee !== null) {
      throw $result->ee;
    }
    throw new \Exception("change_phone failed: unknown result");
  }

  public function get_uid($appname, $phonenum)
  {
    $this->send_get_uid($appname, $phonenum);
    return $this->recv_get_uid();
  }

  public function send_get_uid($appname, $phonenum)
  {
    $args = new \yiduoyun\phone\PhoneService_get_uid_args();
    $args->appname = $appname;
    $args->phonenum = $phonenum;
    $bin_accel = ($this->output_ instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'get_uid', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('get_uid', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_get_uid()
  {
    $bin_accel = ($this->input_ instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\yiduoyun\phone\PhoneService_get_uid_result', $this->input_->isStrictRead());
    else
    {
      $rseqid = 0;
      $fname = null;
      $mtype = 0;

      $this->input_->readMessageBegin($fname, $mtype, $rseqid);
      if ($mtype == TMessageType::EXCEPTION) {
        $x = new TApplicationException();
        $x->read($this->input_);
        $this->input_->readMessageEnd();
        throw $x;
      }
      $result = new \yiduoyun\phone\PhoneService_get_uid_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    if ($result->ae !== null) {
      throw $result->ae;
    }
    if ($result->ne !== null) {
      throw $result->ne;
    }
    throw new \Exception("get_uid failed: unknown result");
  }

  public function get_phone($appname, $uid)
  {
    $this->send_get_phone($appname, $uid);
    return $this->recv_get_phone();
  }

  public function send_get_phone($appname, $uid)
  {
    $args = new \yiduoyun\phone\PhoneService_get_phone_args();
    $args->appname = $appname;
    $args->uid = $uid;
    $bin_accel = ($this->output_ instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'get_phone', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('get_phone', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_get_phone()
  {
    $bin_accel = ($this->input_ instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\yiduoyun\phone\PhoneService_get_phone_result', $this->input_->isStrictRead());
    else
    {
      $rseqid = 0;
      $fname = null;
      $mtype = 0;

      $this->input_->readMessageBegin($fname, $mtype, $rseqid);
      if ($mtype == TMessageType::EXCEPTION) {
        $x = new TApplicationException();
        $x->read($this->input_);
        $this->input_->readMessageEnd();
        throw $x;
      }
      $result = new \yiduoyun\phone\PhoneService_get_phone_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    if ($result->ae !== null) {
      throw $result->ae;
    }
    if ($result->ne !== null) {
      throw $result->ne;
    }
    throw new \Exception("get_phone failed: unknown result");
  }

}

// HELPER FUNCTIONS AND STRUCTURES

class PhoneService_add_phone_args {
  static $_TSPEC;

  public $appname = null;
  public $uid = null;
  public $phonenum = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'appname',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'uid',
          'type' => TType::STRING,
          ),
        3 => array(
          'var' => 'phonenum',
          'type' => TType::STRING,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['appname'])) {
        $this->appname = $vals['appname'];
      }
      if (isset($vals['uid'])) {
        $this->uid = $vals['uid'];
      }
      if (isset($vals['phonenum'])) {
        $this->phonenum = $vals['phonenum'];
      }
    }
  }

  public function getName() {
    return 'PhoneService_add_phone_args';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->appname);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->uid);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->phonenum);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('PhoneService_add_phone_args');
    if ($this->appname !== null) {
      $xfer += $output->writeFieldBegin('appname', TType::STRING, 1);
      $xfer += $output->writeString($this->appname);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->uid !== null) {
      $xfer += $output->writeFieldBegin('uid', TType::STRING, 2);
      $xfer += $output->writeString($this->uid);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->phonenum !== null) {
      $xfer += $output->writeFieldBegin('phonenum', TType::STRING, 3);
      $xfer += $output->writeString($this->phonenum);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class PhoneService_add_phone_result {
  static $_TSPEC;

  public $success = null;
  public $ae = null;
  public $ee = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        0 => array(
          'var' => 'success',
          'type' => TType::BOOL,
          ),
        1 => array(
          'var' => 'ae',
          'type' => TType::STRUCT,
          'class' => '\yiduoyun\phone\AppNameInvalidException',
          ),
        2 => array(
          'var' => 'ee',
          'type' => TType::STRUCT,
          'class' => '\yiduoyun\phone\AlreadyExistsException',
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['success'])) {
        $this->success = $vals['success'];
      }
      if (isset($vals['ae'])) {
        $this->ae = $vals['ae'];
      }
      if (isset($vals['ee'])) {
        $this->ee = $vals['ee'];
      }
    }
  }

  public function getName() {
    return 'PhoneService_add_phone_result';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 0:
          if ($ftype == TType::BOOL) {
            $xfer += $input->readBool($this->success);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 1:
          if ($ftype == TType::STRUCT) {
            $this->ae = new \yiduoyun\phone\AppNameInvalidException();
            $xfer += $this->ae->read($input);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRUCT) {
            $this->ee = new \yiduoyun\phone\AlreadyExistsException();
            $xfer += $this->ee->read($input);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('PhoneService_add_phone_result');
    if ($this->success !== null) {
      $xfer += $output->writeFieldBegin('success', TType::BOOL, 0);
      $xfer += $output->writeBool($this->success);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->ae !== null) {
      $xfer += $output->writeFieldBegin('ae', TType::STRUCT, 1);
      $xfer += $this->ae->write($output);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->ee !== null) {
      $xfer += $output->writeFieldBegin('ee', TType::STRUCT, 2);
      $xfer += $this->ee->write($output);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class PhoneService_change_phone_args {
  static $_TSPEC;

  public $appname = null;
  public $uid = null;
  public $phonenum = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'appname',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'uid',
          'type' => TType::STRING,
          ),
        3 => array(
          'var' => 'phonenum',
          'type' => TType::STRING,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['appname'])) {
        $this->appname = $vals['appname'];
      }
      if (isset($vals['uid'])) {
        $this->uid = $vals['uid'];
      }
      if (isset($vals['phonenum'])) {
        $this->phonenum = $vals['phonenum'];
      }
    }
  }

  public function getName() {
    return 'PhoneService_change_phone_args';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->appname);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->uid);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->phonenum);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('PhoneService_change_phone_args');
    if ($this->appname !== null) {
      $xfer += $output->writeFieldBegin('appname', TType::STRING, 1);
      $xfer += $output->writeString($this->appname);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->uid !== null) {
      $xfer += $output->writeFieldBegin('uid', TType::STRING, 2);
      $xfer += $output->writeString($this->uid);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->phonenum !== null) {
      $xfer += $output->writeFieldBegin('phonenum', TType::STRING, 3);
      $xfer += $output->writeString($this->phonenum);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class PhoneService_change_phone_result {
  static $_TSPEC;

  public $success = null;
  public $ae = null;
  public $ne = null;
  public $ee = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        0 => array(
          'var' => 'success',
          'type' => TType::BOOL,
          ),
        1 => array(
          'var' => 'ae',
          'type' => TType::STRUCT,
          'class' => '\yiduoyun\phone\AppNameInvalidException',
          ),
        2 => array(
          'var' => 'ne',
          'type' => TType::STRUCT,
          'class' => '\yiduoyun\phone\NotFoundException',
          ),
        3 => array(
          'var' => 'ee',
          'type' => TType::STRUCT,
          'class' => '\yiduoyun\phone\AlreadyExistsException',
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['success'])) {
        $this->success = $vals['success'];
      }
      if (isset($vals['ae'])) {
        $this->ae = $vals['ae'];
      }
      if (isset($vals['ne'])) {
        $this->ne = $vals['ne'];
      }
      if (isset($vals['ee'])) {
        $this->ee = $vals['ee'];
      }
    }
  }

  public function getName() {
    return 'PhoneService_change_phone_result';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 0:
          if ($ftype == TType::BOOL) {
            $xfer += $input->readBool($this->success);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 1:
          if ($ftype == TType::STRUCT) {
            $this->ae = new \yiduoyun\phone\AppNameInvalidException();
            $xfer += $this->ae->read($input);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRUCT) {
            $this->ne = new \yiduoyun\phone\NotFoundException();
            $xfer += $this->ne->read($input);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::STRUCT) {
            $this->ee = new \yiduoyun\phone\AlreadyExistsException();
            $xfer += $this->ee->read($input);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('PhoneService_change_phone_result');
    if ($this->success !== null) {
      $xfer += $output->writeFieldBegin('success', TType::BOOL, 0);
      $xfer += $output->writeBool($this->success);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->ae !== null) {
      $xfer += $output->writeFieldBegin('ae', TType::STRUCT, 1);
      $xfer += $this->ae->write($output);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->ne !== null) {
      $xfer += $output->writeFieldBegin('ne', TType::STRUCT, 2);
      $xfer += $this->ne->write($output);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->ee !== null) {
      $xfer += $output->writeFieldBegin('ee', TType::STRUCT, 3);
      $xfer += $this->ee->write($output);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class PhoneService_get_uid_args {
  static $_TSPEC;

  public $appname = null;
  public $phonenum = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'appname',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'phonenum',
          'type' => TType::STRING,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['appname'])) {
        $this->appname = $vals['appname'];
      }
      if (isset($vals['phonenum'])) {
        $this->phonenum = $vals['phonenum'];
      }
    }
  }

  public function getName() {
    return 'PhoneService_get_uid_args';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->appname);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->phonenum);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('PhoneService_get_uid_args');
    if ($this->appname !== null) {
      $xfer += $output->writeFieldBegin('appname', TType::STRING, 1);
      $xfer += $output->writeString($this->appname);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->phonenum !== null) {
      $xfer += $output->writeFieldBegin('phonenum', TType::STRING, 2);
      $xfer += $output->writeString($this->phonenum);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class PhoneService_get_uid_result {
  static $_TSPEC;

  public $success = null;
  public $ae = null;
  public $ne = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        0 => array(
          'var' => 'success',
          'type' => TType::STRING,
          ),
        1 => array(
          'var' => 'ae',
          'type' => TType::STRUCT,
          'class' => '\yiduoyun\phone\AppNameInvalidException',
          ),
        2 => array(
          'var' => 'ne',
          'type' => TType::STRUCT,
          'class' => '\yiduoyun\phone\NotFoundException',
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['success'])) {
        $this->success = $vals['success'];
      }
      if (isset($vals['ae'])) {
        $this->ae = $vals['ae'];
      }
      if (isset($vals['ne'])) {
        $this->ne = $vals['ne'];
      }
    }
  }

  public function getName() {
    return 'PhoneService_get_uid_result';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 0:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->success);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 1:
          if ($ftype == TType::STRUCT) {
            $this->ae = new \yiduoyun\phone\AppNameInvalidException();
            $xfer += $this->ae->read($input);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRUCT) {
            $this->ne = new \yiduoyun\phone\NotFoundException();
            $xfer += $this->ne->read($input);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('PhoneService_get_uid_result');
    if ($this->success !== null) {
      $xfer += $output->writeFieldBegin('success', TType::STRING, 0);
      $xfer += $output->writeString($this->success);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->ae !== null) {
      $xfer += $output->writeFieldBegin('ae', TType::STRUCT, 1);
      $xfer += $this->ae->write($output);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->ne !== null) {
      $xfer += $output->writeFieldBegin('ne', TType::STRUCT, 2);
      $xfer += $this->ne->write($output);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class PhoneService_get_phone_args {
  static $_TSPEC;

  public $appname = null;
  public $uid = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'appname',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'uid',
          'type' => TType::STRING,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['appname'])) {
        $this->appname = $vals['appname'];
      }
      if (isset($vals['uid'])) {
        $this->uid = $vals['uid'];
      }
    }
  }

  public function getName() {
    return 'PhoneService_get_phone_args';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->appname);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->uid);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('PhoneService_get_phone_args');
    if ($this->appname !== null) {
      $xfer += $output->writeFieldBegin('appname', TType::STRING, 1);
      $xfer += $output->writeString($this->appname);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->uid !== null) {
      $xfer += $output->writeFieldBegin('uid', TType::STRING, 2);
      $xfer += $output->writeString($this->uid);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class PhoneService_get_phone_result {
  static $_TSPEC;

  public $success = null;
  public $ae = null;
  public $ne = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        0 => array(
          'var' => 'success',
          'type' => TType::STRING,
          ),
        1 => array(
          'var' => 'ae',
          'type' => TType::STRUCT,
          'class' => '\yiduoyun\phone\AppNameInvalidException',
          ),
        2 => array(
          'var' => 'ne',
          'type' => TType::STRUCT,
          'class' => '\yiduoyun\phone\NotFoundException',
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['success'])) {
        $this->success = $vals['success'];
      }
      if (isset($vals['ae'])) {
        $this->ae = $vals['ae'];
      }
      if (isset($vals['ne'])) {
        $this->ne = $vals['ne'];
      }
    }
  }

  public function getName() {
    return 'PhoneService_get_phone_result';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 0:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->success);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 1:
          if ($ftype == TType::STRUCT) {
            $this->ae = new \yiduoyun\phone\AppNameInvalidException();
            $xfer += $this->ae->read($input);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRUCT) {
            $this->ne = new \yiduoyun\phone\NotFoundException();
            $xfer += $this->ne->read($input);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('PhoneService_get_phone_result');
    if ($this->success !== null) {
      $xfer += $output->writeFieldBegin('success', TType::STRING, 0);
      $xfer += $output->writeString($this->success);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->ae !== null) {
      $xfer += $output->writeFieldBegin('ae', TType::STRUCT, 1);
      $xfer += $this->ae->write($output);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->ne !== null) {
      $xfer += $output->writeFieldBegin('ne', TType::STRUCT, 2);
      $xfer += $this->ne->write($output);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


