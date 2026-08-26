<?php
/**
 * ip2region xdb 离线 IP 归属库查询器（MIT，算法源自官方 ip2region 开源项目）
 * ----------------------------------------------------------
 * 用法：
 *   require __DIR__ . '/ip2region_searcher.php';
 *   $s = new Ip2regionSearcher('/www/wwwroot/ding/static/ip2region.xdb');
 *   $region = $s->search('1.2.3.4');   // 返回 "中国|北京|北京市|电信" 或 null
 *
 * 部署：把 ip2region.xdb（约 11MB）下载到 /www/wwwroot/ding/static/ip2region.xdb 即自动启用，
 *      无需 API key、离线可用、精确到市。下载地址：
 *      https://github.com/lionsoul2014/ip2region 的 data/ip2region.xdb（release 里可下载）
 *
 * xdb 文件格式（官方）：
 *   [0,256)      header（前 16B：version4 + headerLen4 + indexStartPtr4 + indexEndPtr4）
 *   [256,4352)   向量索引（256 段 × 16B：startIp4 + idxPtrStart4 + idxPtrEnd4）
 *   索引区        每记录 14B：startIp4 + endIp4 + dataPtr4 + dataLen2
 *   数据区        UTF-8：国家|省|市|区县|ISP
 */
class Ip2regionSearcher
{
    private $fp;
    private $idxStart;
    private $idxEnd;
    private $vecStart = 256;

    public function __construct(string $dbPath)
    {
        if (!is_file($dbPath) || !is_readable($dbPath)) {
            throw new RuntimeException('ip2region xdb 不可读: ' . $dbPath);
        }
        $this->fp = fopen($dbPath, 'rb');
        $meta = unpack('N4', (string)fread($this->fp, 16));
        $this->idxStart = $meta[3];
        $this->idxEnd   = $meta[4];
    }

    /** 点分 IP 转 uint32 */
    private function ipToLong(string $ip): int
    {
        $p = array_map('intval', explode('.', $ip));
        return (($p[0] * 256 + $p[1]) * 256 + $p[2]) * 256 + $p[3];
    }

    /** 查询 IP 归属，返回 "国家|省|市|区县|ISP" 字符串，查不到返回 null */
    public function search(string $ip): ?string
    {
        $ipn = $this->ipToLong($ip);
        // 向量段：ip 高 8 位
        $seg = $ipn >> 24;
        fseek($this->fp, $this->vecStart + $seg * 16);
        $vec = unpack('N4', (string)fread($this->fp, 16));
        $segStart = $vec[2];
        $segEnd   = $vec[3];
        if ($segStart === 0 || $segEnd < $segStart) {
            return null;
        }
        // 索引区二分（向量段内 ptr 为索引区记录序号）
        $lo = $segStart;
        $hi = $segEnd;
        while ($lo <= $hi) {
            $mid = intdiv($lo + $hi, 2);
            $pos = $this->idxStart + $mid * 14;
            fseek($this->fp, $pos);
            $rec = unpack('N3n', (string)fread($this->fp, 14));
            $sIp = $rec[1];
            $eIp = $rec[2];
            if ($ipn < $sIp) {
                $hi = $mid - 1;
            } elseif ($ipn > $eIp) {
                $lo = $mid + 1;
            } else {
                fseek($this->fp, $rec[3]);
                $data = fread($this->fp, $rec[4]);
                return $data === false || trim($data) === '' ? null : (string)$data;
            }
        }
        return null;
    }

    public function __destruct()
    {
        if (is_resource($this->fp)) {
            fclose($this->fp);
        }
    }
}
