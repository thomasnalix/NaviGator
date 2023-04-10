import concurrent.futures
import json
import os

import psycopg2 as psycopg2

# Imaginez avoir un .env (c'est pour les faibles)
bdd_name = "iut"
hostname = "162.38.222.142"
login = "nalixt"
password = "05092022"
output_dir = './data'

if not os.path.exists(output_dir):
    os.makedirs(output_dir)

sqlQuery = '''
            SELECT *
            FROM nalixt.vitesse
            WHERE num_departement_depart = %s
            OR
            num_departement_arrivee = %s;
           '''

conn = psycopg2.connect(host=hostname, database=bdd_name, user=login, password=password)

departments = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17',
               '18', '19', '2A', '2B', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33',
               '34', '35', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46', '47', '48', '49', '50',
               '51', '52', '53', '54', '55', '56', '57', '58', '59', '60', '61', '62', '63', '64', '65', '66', '67',
               '68', '69', '70', '71', '72', '73', '74', '75', '76', '77', '78', '79', '80', '81', '82', '83', '84',
               '85', '86', '87', '88', '89', '90', '91', '92', '93', '94', '95']


def process_department(department):
    cur = conn.cursor()
    cur.execute(sqlQuery, (department, department))
    with open(f'{output_dir}/{department}.json', 'w') as f:
        f.truncate(0)
        content = cur.fetchall()
        nodes = {}
        for node in content:
            noeud_depart_gid = node[0]
            noeud_depart_long = node[1]
            noeud_depart_lat = node[2]
            noeud_arrivee_gid = node[3]
            noeud_arrivee_long = node[4]
            noeud_arrivee_lat = node[5]
            troncon_gid = node[6]
            longueur_troncon = node[7]
            num_departement_depart = node[8]
            num_departement_arrivee = node[9]
            vitesse = node[10]

            if num_departement_depart == department:
                nodes.setdefault(department, {}).setdefault(noeud_depart_gid, []).append({
                    "noeud_gid": noeud_arrivee_gid,
                    "noeud_courant_lat": noeud_depart_lat,
                    "noeud_courant_long": noeud_depart_long,
                    "noeud_coord_lat": noeud_arrivee_lat,
                    "noeud_coord_long": noeud_arrivee_long,
                    "troncon_gid": troncon_gid,
                    "longueur_troncon": longueur_troncon,
                    "vitesse": vitesse,
                })
            if num_departement_arrivee == department:
                nodes.setdefault(department, {}).setdefault(noeud_arrivee_gid, []).append({
                    "noeud_gid": noeud_depart_gid,
                    "noeud_courant_lat": noeud_arrivee_lat,
                    "noeud_courant_long": noeud_arrivee_long,
                    "noeud_coord_lat": noeud_depart_lat,
                    "noeud_coord_long": noeud_depart_long,
                    "troncon_gid": troncon_gid,
                    "longueur_troncon": longueur_troncon,
                    "vitesse": vitesse,
                })

        f.write(json.dumps(nodes))

    cur.close()
    return department


def generate_departments():
    with concurrent.futures.ThreadPoolExecutor() as executor:
        futures = []
        for dep in departments:
            futures.append(executor.submit(process_department, dep))
        for future in concurrent.futures.as_completed(futures):
            result = future.result()
            print(f'Department loaded: {result}.json')


if __name__ == '__main__':
    generate_departments()
