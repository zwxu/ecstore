<?php

/*
require('../interface/queue.php');
*/
class base_queue_rabbitmq implements base_interface_queue
{
    private $connect;
    private $channel;
    private $exchange;
    private $queue;
    
    public function __construct()
    {
        $this->connect = new AMQPConnection();
        $this->connect->connect();

        $this->channel = new AMQPChannel($this->connect);
        if (!$this->channel->isConnected()) {
            trigger_error( "channel is disconnect!\n",E_USER_ERROR );
        }
        $this->channel->setPrefetchCount(1);

        $this->exchange = new AMQPExchange($this->channel);
        $this->exchange->setName('ecos_exchange');
        $this->exchange->setType(AMQP_EX_TYPE_FANOUT);
        $this->exchange->declare();

        $this->queue = new AMQPQueue($this->channel);
        $this->queue->setName('queue');
        $this->queue->setFlags(AMQP_DURABLE);
        $this->queue->declare();
        $this->queue->bind('ecos_exchange', 'task_queue');

    }

    public function publish($message)
    {
        $message = serialize($message);
        return $this->exchange->publish($message, 'task_queue', AMQP_NOPARAM, array('delivery_mode'=>2));
    }

    public function consume()
    {
        set_time_limit(0);
        ignore_user_abort(true);
        $this->queue->consume(array($this,'callback')); 
    }
    
    public function callback($envelope, $queue)
    {
        $errmsg = null;
        $data = unserialize( $envelope->getBody() ); 
        if(!isset($data['worker'])){
            return false;
        }
        list($worker, $method) = explode('.', $data['worker']);
        call_user_func_array( array(  $worker ,$method),array(&$data['cursor_id'],$data['params'], &$errmsg));
        kernel::log('Spawn [Task-'.$worker.'.'.$method.']');
        if(is_null($errmsg)) {
            $queue->ack($envelope->getDeliveryTag());
            return true;
        }/* else {
            $queue->nack($envelope->getDeliveryTag());
            kernel::log($errmsg);
        } */   
        kernel::log($errmsg);
        return false;
    }    

}
/*
$queue = new base_queue_rabbitmq();
$queue->publish('value');
$queue->publish('value');
$queue->publish('value');
$queue->publish('value');
$queue->consume();
*/
