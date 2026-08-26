<?php /** 首页：联系我们 */
$phone   = $S['contact_phone']   ?? $S['phone']    ?? '';
$phone2  = $S['contact_phone2']  ?? '';
$wxQr    = $S['contact_wx_qr']   ?? '';
$mpQr    = $S['contact_mp_qr']   ?? '';
$email   = $S['email']           ?? '';
$address = $S['address']         ?? '';
$hm = []; if (!empty($settings['home_modules'])) { $hm = json_decode($settings['home_modules'], true) ?: []; }
$ct = $hm['contact'] ?? [];
$kicker   = $ct['kicker']    ?? 'CONTACT US';
$title    = $ct['title']     ?? '联系我们 · <em>随时对话</em>';
$desc     = $ct['desc']      ?? '不管你是想了解开源部署，还是电商孵化一对一陪跑，留下信息或直接联系我们，都会有人回应。';
$ctaText  = trim((string)($ct['cta_text'] ?? ''));
$ctaUrl   = trim((string)($ct['cta_url']  ?? ''));
$ctaType  = trim((string)($ct['cta_type'] ?? '')); // 旧版显式类型（可选），新版由 URL 自动推断
$subText  = trim((string)($ct['sub_text'] ?? ''));
$subUrl   = trim((string)($ct['sub_url']  ?? ''));
$subType  = trim((string)($ct['sub_type'] ?? ''));
// 按钮类型：显式指定优先，否则按 URL 推断（download 后缀 → 下载）
function _ct_btn_type(string $url, string $explicit): string {
    if (in_array($explicit, ['download','form','link'], true)) return $explicit;
    return preg_match('/\.(zip|rar|7z|pdf|docx?|xlsx?|pptx?|apk|exe)(?:[?#]|$)/i', $url) ? 'download' : 'link';
}
// 把 type 转成 HTML 属性 / <a> 的额外属性
function _ct_attrs(string $url, string $type): string {
    if ($type === 'download' && $url !== '') return ' download="' . e(basename(parse_url($url, PHP_URL_PATH) ?: 'file')) . '"';
    return '';
}
$hasCta = $ctaText !== '' && $ctaUrl !== '';
$hasSub = $subText !== '' && $subUrl !== '';
?>
<section class="q-section q-contact">
  <div class="q-container q-reveal">
    <div class="head-row">
      <div>
        <span class="q-kicker"><?= e($kicker) ?></span>
        <h2 class="q-title"><?= $title ?></h2>
        <p class="q-desc"><?= e($desc) ?></p>
      </div>
      <?php if ($hasCta || $hasSub): ?>
      <div class="q-contact__btns">
        <?php if ($hasCta): ?>
          <a class="q-btn q-btn--grad" href="<?= e($ctaUrl) ?>"<?= _ct_attrs($ctaUrl, _ct_btn_type($ctaUrl, $ctaType)) ?>><?= e($ctaText) ?></a>
        <?php endif; ?>
        <?php if ($hasSub): ?>
          <a class="q-btn q-btn--ghost" href="<?= e($subUrl) ?>"<?= _ct_attrs($subUrl, _ct_btn_type($subUrl, $subType)) ?> style="margin-left:8px"><?= e($subText) ?></a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
    <div class="q-contact__grid">
      <div class="q-contact__info">
        <?php if ($phone): ?>
        <div class="q-contact__row">
          <i>📞</i>
          <div>
            <div class="q-contact__label">服务电话</div>
            <a class="q-contact__value" href="tel:<?= e($phone) ?>"><?= e($phone) ?></a>
          </div>
        </div>
        <?php endif; ?>
        <?php if ($phone2): ?>
        <div class="q-contact__row">
          <i>💬</i>
          <div>
            <div class="q-contact__label">导师电话</div>
            <a class="q-contact__value" href="tel:<?= e($phone2) ?>"><?= e($phone2) ?></a>
          </div>
        </div>
        <?php endif; ?>
        <?php if ($email): ?>
        <div class="q-contact__row">
          <i>✉️</i>
          <div>
            <div class="q-contact__label">邮箱</div>
            <a class="q-contact__value" href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
          </div>
        </div>
        <?php endif; ?>
        <?php if ($address): ?>
        <div class="q-contact__row">
          <i>📍</i>
          <div>
            <div class="q-contact__label">地址</div>
            <div class="q-contact__value"><?= e($address) ?></div>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <div class="q-contact__qrs">
        <div class="q-contact__qr">
          <?php if ($wxQr): ?>
            <img src="<?= e($wxQr) ?>" alt="负责人微信二维码">
          <?php else: ?>
            <div class="q-contact__qr-ph">微信二维码</div>
          <?php endif; ?>
          <span>负责人微信</span>
        </div>
        <div class="q-contact__qr">
          <?php if ($mpQr): ?>
            <img src="<?= e($mpQr) ?>" alt="微信公众号二维码">
          <?php else: ?>
            <div class="q-contact__qr-ph">公众号二维码</div>
          <?php endif; ?>
          <span>微信公众号</span>
        </div>
      </div>
    </div>
  </div>
</section>
