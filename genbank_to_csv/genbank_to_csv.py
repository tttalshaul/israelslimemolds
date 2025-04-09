from Bio import SeqIO
import csv
import re
from datetime import datetime

def format_date(d):
    return datetime.strptime(d, '%d-%b-%Y').strftime('%m/%d/%Y')


file_path = '/Users/user/Downloads/sequence.gb'
genbank_object = SeqIO.parse(open(file_path, 'r'), "genbank")
genes=list(genbank_object)

with open('/Users/user/Downloads/myxo_genes.csv', 'w', newline='') as csvfile:
    gene_writer = csv.writer(csvfile, quoting=csv.QUOTE_MINIMAL)
    gene_writer.writerow([
        "Organism", "Date", "Accession", "Description", 
        "Reference Title", "Reference Authors", "Types", 
        "PCR primer fwd name", "PCR primer fwd seq", 
        "PCR primer rev name", "PCR primer rev seq"])
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
        pcr_primers = []
        for feature in gene.features:
            if 'PCR_primers' in feature.qualifiers:
                for pcr_primer in feature.qualifiers['PCR_primers']:
                    pcr_primers.append(pcr_primer)
        pcr_primers = ", ".join(list(set(pcr_primers)))
        pcr_primers_fwd_name = ", ".join(
            re.findall(r"fwd_name: ([a-zA-Z0-9' _-]+),", pcr_primers))
        pcr_primers_fwd_seq = ", ".join(
            re.findall(r"fwd_seq: ([a-z]+)", pcr_primers))
        pcr_primers_rev_name = ", ".join(
            re.findall(r"rev_name: ([a-zA-Z0-9' _-]+),", pcr_primers))
        pcr_primers_rev_seq = ", ".join(
            re.findall(r"rev_seq: ([a-z]+)", pcr_primers))
        gene_writer.writerow([
            taxon, date, accession, description, title, authors, types, 
            pcr_primers_fwd_name, pcr_primers_fwd_seq, pcr_primers_rev_name, 
            pcr_primers_rev_seq])
