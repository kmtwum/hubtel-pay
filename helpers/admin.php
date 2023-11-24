<?php

    class admin {

        function reg() {
            add_filter('plugin_action_links_hubtel-pay/hubtel-pay.php', [$this, 'manage']);
            add_action( 'plugins_loaded', 'init_your_gateway_class' );
        }

        function manage($links) {
            $link = "<a href='admin.php?page=hubtel-pay'>Manage</a>";
            array_push($links, $link);
            return $links;

        }

    }
