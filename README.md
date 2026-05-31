# TaskCloud — AWS High Availability Web App

**Proyecto Final · Computación en la Nube · UAO 2026**

Sistema de gestión de tareas desplegado en AWS con alta disponibilidad,
balanceo de carga y escalado horizontal.

## URL de la aplicación
http://taskcloud-alb-2044196625.us-east-1.elb.amazonaws.com

## Arquitectura
- VPC con 4 subredes en 2 AZs (us-east-1a, us-east-1b)
- Application Load Balancer + Auto Scaling Group (min=2, max=4)
- Amazon RDS MySQL 8.0 en subred privada
- AWS Secrets Manager para credenciales
- Ubuntu 26.04 + Apache2 + PHP

## Estructura
- `app/` — Código fuente PHP de TaskCloud
- `scripts/` — Scripts user-data para EC2
- `docs/` — Informe final PDF y fuente LaTeX

## Autores
- Diego Fernando Teuta Henao
- Luis Santiago Osorio Ortiz

**Profesora:** Anabel Montero Posada
EOF
