<?php
namespace wapmorgan\ServerAvailabilityMonitor\Servers;

class MemcacheServer extends BaseServer {
	const DEFAULT_PORT = '11211';

    /**
     * @param $timeOut
     * @return bool|\RuntimeException
     */
    public function checkAvailability($timeOut) {
		if (class_exists('\Memcache')) {
			$memcache = new \Memcache();
			if (@$memcache->connect($this->hostname, $this->port, $timeOut) === false) return new \RuntimeException('Memcache server is not available');
			else return true;
		} else if (class_exists('\Memcached')) {
			$memcached = new \Memcached();
			$memcached->setOption(Memcached::OPT_CONNECT_TIMEOUT, $timeOut * 1000);
			$memcached->addServer($this->hostname, $this->port);
			$version = @$memcached->getVersion();
			if (in_array(current($version), [false, '255.255.255'])) return new \RuntimeException('Memcache server is not available');
			else return true;
		}
		return new \RuntimeException('No available memcache connectors found.');
	}

    /**
     * @return string
     */
    public function getServerHash() {
		return md5($this->hostname.':'.$this->port);
	}
}
