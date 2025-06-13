import sys, os
from PIL import Image
import imagehash
import json

def get_phash(image_path):
    image = Image.open(image_path)
    return imagehash.phash(image)

def is_duplicate(phash, existing, threshold = 9):
    return phash - existing < threshold

if __name__ == "__main__":
    imagesFolder = sys.argv[1]
    existing_hashes = [imagehash.hex_to_hash(h) for h in json.loads(sys.argv[2])]

    duplicates = []
    originalHashes = []

    for image in os.listdir(imagesFolder):
        path = os.path.join(imagesFolder, image)

        if path.lower().endswith('.heic'):
            os.remove(path)
            continue

        phash = get_phash(path)
        isOriginal = True

        for existing in existing_hashes:
            if (is_duplicate(phash, existing)):
                duplicates.append(path)
                isOriginal = False
                break

        if isOriginal:
            originalHashes.append(phash.__str__())

    print(json.dumps({
        'duplicates': duplicates,
        'originalHashes': originalHashes
    }))
