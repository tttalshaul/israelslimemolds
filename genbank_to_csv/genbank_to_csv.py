from Bio import SeqIO
import csv
from datetime import datetime

def format_date(d):
    return datetime.strptime(d, '%d-%b-%Y').strftime('%m/%d/%Y')


file_path = '/Users/user/Downloads/sequence.gb'
genbank_object = SeqIO.parse(open(file_path, 'r'), "genbank")
genes=list(genbank_object)

with open('/Users/user/Downloads/myxo_genes.csv', 'w', newline='') as csvfile:
    gene_writer = csv.writer(csvfile, quoting=csv.QUOTE_MINIMAL)
    for gene in genes:
        taxon = gene.annotations['organism']
        date = format_date(gene.annotations['date'])
        accession = gene.id
        description = gene.description
        references = gene.annotations['references']
        full_reference = None
        for reference in references:
            if not full_reference or len(full_reference.title) < len(reference.title):
                full_reference = reference
        title = full_reference.title
        authors = full_reference.authors
        types = ", ".join(list(set([feature.type for feature in gene.features])))
        gene_writer.writerow([taxon, date, accession, description, title, authors, types])
