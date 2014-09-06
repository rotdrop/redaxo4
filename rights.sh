#! /bin/bash
chown -R claus:apache . ; chmod -R gu+rwX . ; find . -type d -exec chmod g+s {} \;
