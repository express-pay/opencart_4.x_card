-- Регистрация расширения Express Pay Card для OpenCart 4
-- Выполнить после копирования папки expresspay_card в upload/extension/

-- 1. Регистрация пути (чтобы расширение появилось в списке админки)
INSERT INTO `oc_extension_path` (`extension_install_id`, `path`) VALUES
(0, 'expresspay_card/admin/controller/payment/card_expresspay.php');

-- 2. Регистрация расширения (для catalog-side autoloader и статуса "установлено")
INSERT INTO `oc_extension` (`extension`, `type`, `code`) VALUES
('expresspay_card', 'payment', 'card_expresspay');

-- 3. Регистрация install-записи (для admin-side autoloader)
INSERT INTO `oc_extension_install` (`extension_id`, `extension_download_id`, `name`, `code`, `version`, `author`, `link`, `status`, `date_added`) VALUES
(0, 0, 'Express Pay Card', 'expresspay_card', '1.0.1', 'Express Pay', '', 1, NOW());
