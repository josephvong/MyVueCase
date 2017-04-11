#!/bin/sh

# 文件所在目录
dname=$(cd `dirname $0`;pwd)

chown root:daemon ${dname}
chmod 0770 ${dname}

chown root:daemon ${dname}/conf
chmod 0770 ${dname}/conf

