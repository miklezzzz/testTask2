#!/usr/bin/env bash

NAMESPACE=$1

kubectl get ns $NAMESPACE --insecure-skip-tls-verify=true
if [ $? -ne 0 ]
then
    kubectl create ns $NAMESPACE --insecure-skip-tls-verify=true 
    kubectl get secret -n databases mysql -o yaml --export --insecure-skip-tls-verify=true | kubectl apply -n $NAMESPACE --insecure-skip-tls-verify=true -f - 
    cat <<EOF | kubectl --insecure-skip-tls-verify=true apply -n $NAMESPACE -f -
apiVersion: v1
items:
- apiVersion: v1
  kind: Service
  metadata:
    name: mongodb
  spec:
    externalName: mongodb.databases.svc.cluster.local
    ports:
    - name: mongodb
      port: 27017
      protocol: TCP
      targetPort: mongodb
    sessionAffinity: None
    type: ExternalName
---
- apiVersion: v1
  kind: Service
  metadata:
    name: mysql
  spec:
    externalName: mysql.databases.svc.cluster.local
    ports:
    - name: mysql
      port: 3306
      protocol: TCP
      targetPort: mysql
    sessionAffinity: None
    type: ExternalName
---
- apiVersion: v1
  kind: Service
  metadata:
    name: redis-master
  spec:
    externalName: redis-master.databases.svc.cluster.local
    ports:
    - name: redis
      port: 6379
      protocol: TCP
      targetPort: redis
    sessionAffinity: None
    type: ExternalName
EOF
fi
