var ntc = {
        name: function(color) {
            color = color.toUpperCase();
            if (color.length < 3 || color.length > 7) return ["#000000", "Invalid Color"];
            if (color.length % 3 == 0) color = "#" + color;
            if (color.length == 4) color = "#" + color.substr(1, 1) + color.substr(1, 1) + color.substr(2, 1) + color.substr(2, 1) + color.substr(3, 1) + color.substr(3, 1);

            var rgb = parseInt(color.substr(1), 16);
            var r = (rgb >> 16) & 0xff;
            var g = (rgb >> 8) & 0xff;
            var b = (rgb & 0xff);

            return this.getNearestColor(r, g, b);
        },

        getNearestColor: function(r, g, b) {
            var minDistance = Infinity;
            var nearestColor = null;

            for (var i = 0; i < this.colors.length; i++) {
                var c = this.colors[i];
                var rd = c[0] - r;
                var gd = c[1] - g;
                var bd = c[2] - b;
                var distance = rd * rd + gd * gd + bd * bd;

                if (distance < minDistance) {
                    minDistance = distance;
                    nearestColor = c;
                }
            }

            return [this.rgbToHex(nearestColor[0], nearestColor[1], nearestColor[2]), nearestColor[3]];
        },

        rgbToHex: function(r, g, b) {
            return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase();
        },

        colors: [
                [255, 0, 0, "Red"],
                [0, 0, 255, "Blue"],
                [0, 255, 0, "Green"],
                [255, 255, 0, "Yellow"],
                [128, 0, 128, "Purple"],
        [255, 192, 203, "Pink"],
        [255, 165, 0, "Orange"],
        [139, 69, 19, "Brown"],
        [128, 128, 128, "Gray"],
        [0, 0, 128, "Navy"],
        [0, 128, 128, "Teal"],
        [128, 0, 0, "Maroon"],
        [245, 245, 220, "Beige"],
        [0, 255, 255, "Cyan"],
        [255, 0, 255, "Magenta"],
        [192, 192, 192, "Silver"],
        [128, 128, 0, "Olive"],
        [240, 248, 255, "AliceBlue"],
        [250, 235, 215, "AntiqueWhite"],
        [127, 255, 212, "Aquamarine"],
        [240, 255, 255, "Azure"],
        [255, 228, 196, "Bisque"],
        [138, 43, 226, "BlueViolet"],
        [222, 184, 135, "BurlyWood"],
        [95, 158, 160, "CadetBlue"],
        [127, 255, 0, "Chartreuse"],
        [210, 105, 30, "Chocolate"],
        [255, 127, 80, "Coral"],
        [100, 149, 237, "CornflowerBlue"],
        [255, 248, 220, "Cornsilk"],
        [220, 20, 60, "Crimson"],
        [0, 139, 139, "DarkCyan"],
        [184, 134, 11, "DarkGoldenRod"],
        [169, 169, 169, "DarkGray"],
        [0, 100, 0, "DarkGreen"],
        [189, 183, 107, "DarkKhaki"],
        [139, 0, 139, "DarkMagenta"],
        [85, 107, 47, "DarkOliveGreen"]
        // Add more colors as needed
    ]
};
