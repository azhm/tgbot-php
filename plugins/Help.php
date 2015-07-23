<?php

/**
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Help extends Base
{
    static function desc() {
        return "/help - Help plugin. Get info from other plugins.  ";
    }

    static function usage() {
        return array(
            "/help - Show list of plugins.",
            "/help all - Show all commands for every plugin.",
            "/help [plugin name] -  Commands for that plugin.",
        );
    }

    /**
     * 得到说明信息
     * 如果有参数，那么就拿一个的，否则拿取所有的
     * @param null $text
     * @return string
     */
    private function get_helps($text = NULL) {
        $helps    = array();
        $router   = Db::get_router();
        $bot_info = Db::get_bot_info();

        //抓文字里的关键词，抓到是要请求什么插件
        $one = false;
        foreach ($router as $reg => $class) {
            if ($text) {
                $desc = NULL;

                // 如果是单个拿取的话，直接跳出
                if (strtolower($class) == strtolower($text)) {
                    $desc = $class::usage();
                } elseif (preg_match($reg, ('/' . $text), $m)) {
                    CFun::echo_log('Help:get_helps 正则匹配结果: $reg=%s $text=%s $m=%s', $reg, $text, $m);
                    CFun::echo_log('Help:get_helps 正则匹配到的插件: $class=%s', $class);

                    $desc = $class::usage();
                }

                if (!empty($desc)) {
                    if (!is_array($desc)) {
                        $desc = array($desc);
                    }

                    $helps = $desc;
                    $one   = true;
                    break;
                }
            }

            //如果是拿取所有的信息的话
            if (strtolower($text) == 'all') {
                $desc = $class::usage();
            } else {
                $desc = $class::desc();
            }

            if (!is_array($desc)) {
                $desc = array($desc);
            }

            $helps[$class] = $desc;
        }

        if (false == $one) {
            $helps = array_merge(
                array(
                    'Welcome to use ' . $bot_info['show_name'],
                    '',
                ),
                $helps,
                array(
                    '',
                    'GitHub: https://github.com/DrayChou/tgbot-php',
                    'Author: @drayc',
                )
            );
        }

        return implode(PHP_EOL, $helps);
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run() {
        CFun::echo_log("执行 Help run");

        $res_str = $this->get_helps($this->text);
        if (empty($this->text) || strtolower($this->text) == 'all') {
            //发送给个人
            $msg = Telegram::singleton()->send_message(array(
                'chat_id' => $this->from_id,
                'text'    => $res_str,
            ));

            //帮助信息太长的话，就私信给个人
            $res_str = 'I send you a message about it.';
        }

        //发送到群组里
        $msg = Telegram::singleton()->send_message(array(
            'chat_id'             => $this->chat_id,
            'text'                => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
