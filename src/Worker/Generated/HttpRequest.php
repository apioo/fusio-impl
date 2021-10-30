<?php
namespace Fusio\Impl\Worker\Generated;

/**
 * Autogenerated by Thrift Compiler (0.14.2)
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
use Thrift\Protocol\TBinaryProtocolAccelerated;
use Thrift\Exception\TApplicationException;

class HttpRequest
{
    static public $isValidate = false;

    static public $_TSPEC = array(
        1 => array(
            'var' => 'method',
            'isRequired' => false,
            'type' => TType::STRING,
        ),
        2 => array(
            'var' => 'headers',
            'isRequired' => false,
            'type' => TType::MAP,
            'ktype' => TType::STRING,
            'vtype' => TType::STRING,
            'key' => array(
                'type' => TType::STRING,
            ),
            'val' => array(
                'type' => TType::STRING,
                ),
        ),
        3 => array(
            'var' => 'uriFragments',
            'isRequired' => false,
            'type' => TType::MAP,
            'ktype' => TType::STRING,
            'vtype' => TType::STRING,
            'key' => array(
                'type' => TType::STRING,
            ),
            'val' => array(
                'type' => TType::STRING,
                ),
        ),
        4 => array(
            'var' => 'parameters',
            'isRequired' => false,
            'type' => TType::MAP,
            'ktype' => TType::STRING,
            'vtype' => TType::STRING,
            'key' => array(
                'type' => TType::STRING,
            ),
            'val' => array(
                'type' => TType::STRING,
                ),
        ),
        5 => array(
            'var' => 'body',
            'isRequired' => false,
            'type' => TType::STRING,
        ),
    );

    /**
     * @var string
     */
    public $method = null;
    /**
     * @var array
     */
    public $headers = null;
    /**
     * @var array
     */
    public $uriFragments = null;
    /**
     * @var array
     */
    public $parameters = null;
    /**
     * @var string
     */
    public $body = null;

    public function __construct($vals = null)
    {
        if (is_array($vals)) {
            if (isset($vals['method'])) {
                $this->method = $vals['method'];
            }
            if (isset($vals['headers'])) {
                $this->headers = $vals['headers'];
            }
            if (isset($vals['uriFragments'])) {
                $this->uriFragments = $vals['uriFragments'];
            }
            if (isset($vals['parameters'])) {
                $this->parameters = $vals['parameters'];
            }
            if (isset($vals['body'])) {
                $this->body = $vals['body'];
            }
        }
    }

    public function getName()
    {
        return 'HttpRequest';
    }


    public function read($input)
    {
        $xfer = 0;
        $fname = null;
        $ftype = 0;
        $fid = 0;
        $xfer += $input->readStructBegin($fname);
        while (true) {
            $xfer += $input->readFieldBegin($fname, $ftype, $fid);
            if ($ftype == TType::STOP) {
                break;
            }
            switch ($fid) {
                case 1:
                    if ($ftype == TType::STRING) {
                        $xfer += $input->readString($this->method);
                    } else {
                        $xfer += $input->skip($ftype);
                    }
                    break;
                case 2:
                    if ($ftype == TType::MAP) {
                        $this->headers = array();
                        $_size9 = 0;
                        $_ktype10 = 0;
                        $_vtype11 = 0;
                        $xfer += $input->readMapBegin($_ktype10, $_vtype11, $_size9);
                        for ($_i13 = 0; $_i13 < $_size9; ++$_i13) {
                            $key14 = '';
                            $val15 = '';
                            $xfer += $input->readString($key14);
                            $xfer += $input->readString($val15);
                            $this->headers[$key14] = $val15;
                        }
                        $xfer += $input->readMapEnd();
                    } else {
                        $xfer += $input->skip($ftype);
                    }
                    break;
                case 3:
                    if ($ftype == TType::MAP) {
                        $this->uriFragments = array();
                        $_size16 = 0;
                        $_ktype17 = 0;
                        $_vtype18 = 0;
                        $xfer += $input->readMapBegin($_ktype17, $_vtype18, $_size16);
                        for ($_i20 = 0; $_i20 < $_size16; ++$_i20) {
                            $key21 = '';
                            $val22 = '';
                            $xfer += $input->readString($key21);
                            $xfer += $input->readString($val22);
                            $this->uriFragments[$key21] = $val22;
                        }
                        $xfer += $input->readMapEnd();
                    } else {
                        $xfer += $input->skip($ftype);
                    }
                    break;
                case 4:
                    if ($ftype == TType::MAP) {
                        $this->parameters = array();
                        $_size23 = 0;
                        $_ktype24 = 0;
                        $_vtype25 = 0;
                        $xfer += $input->readMapBegin($_ktype24, $_vtype25, $_size23);
                        for ($_i27 = 0; $_i27 < $_size23; ++$_i27) {
                            $key28 = '';
                            $val29 = '';
                            $xfer += $input->readString($key28);
                            $xfer += $input->readString($val29);
                            $this->parameters[$key28] = $val29;
                        }
                        $xfer += $input->readMapEnd();
                    } else {
                        $xfer += $input->skip($ftype);
                    }
                    break;
                case 5:
                    if ($ftype == TType::STRING) {
                        $xfer += $input->readString($this->body);
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

    public function write($output)
    {
        $xfer = 0;
        $xfer += $output->writeStructBegin('HttpRequest');
        if ($this->method !== null) {
            $xfer += $output->writeFieldBegin('method', TType::STRING, 1);
            $xfer += $output->writeString($this->method);
            $xfer += $output->writeFieldEnd();
        }
        if ($this->headers !== null) {
            if (!is_array($this->headers)) {
                throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
            }
            $xfer += $output->writeFieldBegin('headers', TType::MAP, 2);
            $output->writeMapBegin(TType::STRING, TType::STRING, count($this->headers));
            foreach ($this->headers as $kiter30 => $viter31) {
                $xfer += $output->writeString($kiter30);
                $xfer += $output->writeString($viter31);
            }
            $output->writeMapEnd();
            $xfer += $output->writeFieldEnd();
        }
        if ($this->uriFragments !== null) {
            if (!is_array($this->uriFragments)) {
                throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
            }
            $xfer += $output->writeFieldBegin('uriFragments', TType::MAP, 3);
            $output->writeMapBegin(TType::STRING, TType::STRING, count($this->uriFragments));
            foreach ($this->uriFragments as $kiter32 => $viter33) {
                $xfer += $output->writeString($kiter32);
                $xfer += $output->writeString($viter33);
            }
            $output->writeMapEnd();
            $xfer += $output->writeFieldEnd();
        }
        if ($this->parameters !== null) {
            if (!is_array($this->parameters)) {
                throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
            }
            $xfer += $output->writeFieldBegin('parameters', TType::MAP, 4);
            $output->writeMapBegin(TType::STRING, TType::STRING, count($this->parameters));
            foreach ($this->parameters as $kiter34 => $viter35) {
                $xfer += $output->writeString($kiter34);
                $xfer += $output->writeString($viter35);
            }
            $output->writeMapEnd();
            $xfer += $output->writeFieldEnd();
        }
        if ($this->body !== null) {
            $xfer += $output->writeFieldBegin('body', TType::STRING, 5);
            $xfer += $output->writeString($this->body);
            $xfer += $output->writeFieldEnd();
        }
        $xfer += $output->writeFieldStop();
        $xfer += $output->writeStructEnd();
        return $xfer;
    }
}